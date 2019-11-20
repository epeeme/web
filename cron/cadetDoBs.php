<?php

error_reporting(E_ALL);

//require_once "/home/u534143343/public_html/main/classes/Config.class.php";
//require_once "/home/u534143343/public_html/main/classes/DB.class.php";

require_once "../main/classes/Config.class.php";
require_once "../main/classes/DB.class.php";


class cadetDoB extends DB {

    public function parseOphardt($filename) {
    
        $contents = file_get_contents($filename);

        $textToFind = '<table class="table table-striped table-sm rankingbody">'; // This is the start point for the data
        $contents = stristr($contents, $textToFind);                    
        $textToFind = 'all assigned competitions'; // And lastly the footer is surplus to requirements
        $contents = stristr($contents, $textToFind, TRUE);

        $tableRows = preg_split("/  <tr>    /", $contents); 
  
        for ($j = 1; $j < count($tableRows); $j++) {  
    
            $data = [];

            $tableRows[$j] = $this->removeTable($tableRows[$j]);  

            $out = $this->delete_all_between('<div class="modal fade"', '<td title="Nationality', $tableRows[$j]);
            $dataRow = preg_split("/<td/", $out);
     
            $fd = trim($this->get_string_between($dataRow[4], "<a id=\"dLabel".$j."\" class=\"dropdown-toggle\" data-target=\"#\" href=\"#\" data-toggle=\"dropdown\" role=\"button\" aria-haspopup=\"true\" aria-expanded=\"false\">", "</a>"));

            $fencerFirstname = "";
            $fencerSurname = "";    
            $foundSurname = 0;
            $fwid = "";

            $fn = preg_split("/ /", $fd);
      
            for($i=0; $i < count($fn); $i++) { 
                if (ctype_upper($fn[$i])) { 
                    $foundSurname = 1; 
                    $fencerSurname .= $fn[$i]." ";
                } else { 
                    $fencerFirstname .= $fn[$i]." "; 
                }
            }
  
            if ($foundSurname == 0) {
                $fencerSurname = $fn[0];
                $fencerFirstname = "";
                for($i=1; $i < count($fn); $i++) { 
                    if (ucfirst(strtolower($fn[$i])) == $fn[$i]) { 
                        $fencerFirstname .= $fn[$i]." "; 
                    } else { 
                        $fencerSurname .= $fn[$i]." "; 
                    } 
                }
            }
      
            $data['fencerFirstname'] = trim($fencerFirstname);      
            $data['fencerSurname'] = mb_convert_case(trim($fencerSurname), MB_CASE_TITLE, "UTF-8");

            $data['yob'] = trim($this->get_string_between($dataRow[5], "class=\"ranking\">", "</td>"));
                  
            $cd = preg_split("/>/", $dataRow[4]);
            $data['country'] = trim(str_replace("</td","",$cd[count($cd)-2]));
                
            $this->updateFencer($data);
        
        }
    }
    
    private function updateFencer($data) {
        if (($data['yob'] <> '') && ($data['yob'] <> '1900') && ($data['yob'] <> '0000')) {
            $sql = $this->db->prepare('UPDATE fencers
                                       SET yob = :yob 
                                       WHERE (yob IS NULL or yob = \'\') AND fencerFirstname LIKE :firstname AND fencerSurname LIKE :surname');
            $sql->bindValue(":yob", $data['yob']);
            $sql->bindValue(":firstname", $data['fencerFirstname']);
            $sql->bindValue(":surname", $data['fencerSurname']);
            $sql->execute();
        }

        $sql = $this->db->prepare('UPDATE fencers
                                   SET country = :country
                                   WHERE (country IS NULL or country = \'\') AND fencerFirstname LIKE :firstname AND fencerSurname LIKE :surname');
        $sql->bindValue(":country", $data['country']);
        $sql->bindValue(":firstname", $data['fencerFirstname']);
        $sql->bindValue(":surname", $data['fencerSurname']);
        $sql->execute();

    }

    private function removeTable($it) {
        $search = "/[^<table class=\"records_list\">](.*)[^<\/table>]/";
        $replace = "";            
        $start = '<table class="records_list">';
        $end =  '</div>';
        $str = $this->replace_content_inside_delimiters($start, $end, '', $it);                
        return $str;
    }

    private function replace_content_inside_delimiters($start, $end, $new, $source) {
        return preg_replace('#('.preg_quote($start).')(.*?)('.preg_quote($end).')#si', '$1'.$new.'$3', $source);
    }  

    private function get_string_between($string, $start, $end) {
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);   
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }

    private function delete_all_between($beginning, $end, $string) {
        $beginningPos = strpos($string, $beginning);
        $endPos = strpos($string, $end);
        if ($beginningPos === false || $endPos === false) { return $string; }
        $textToDelete = substr($string, $beginningPos, ($endPos + strlen($end)) - $beginningPos);
        return str_replace($textToDelete, '', $string);
    }   
}

$gS = new Config();
$cfg = $gS->getSettings();

$dobs = new cadetDoB($cfg);
$dobs->parseOphardt('https://fencing.ophardt.online/en/display-ranking/html/eur/u17/m/e/i');
$dobs->parseOphardt('https://fencing.ophardt.online/en/display-ranking/html/eur/u17/f/e/i');

?>