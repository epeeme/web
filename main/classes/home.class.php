<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class home extends DB {

    public function getIntro() {        
        $sql = $this->db->prepare('SELECT count(*) FROM results');
        $sql->execute();
        $row['results'] = number_format($sql->fetch(PDO::FETCH_COLUMN));
      
        $sql = $this->db->prepare('SELECT count(*) FROM fencers');
        $sql->execute();
        $row['fencers'] = number_format($sql->fetch(PDO::FETCH_COLUMN));
            
        return $row;   
    }
    
    public function getRecent() {    
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT fullDate AS date1, eventType, eventName, eventDates.ID, eventDates.eventID, DATE_FORMAT(fullDate,\'%D\') AS date2, DATE_FORMAT(fullDate,\'%M\') AS date3, DATE_FORMAT(fullDate,\'%Y\') AS date4
                                   FROM eventDates 
                                   INNER JOIN events ON events.ID = eventDates.eventID 
                                   WHERE fullDate > DATE_SUB(NOW(), INTERVAL 6 MONTH)
                                   ORDER BY fullDate DESC
                                   LIMIT 0, 25');
        $sql->execute();
        $recentData = $sql->fetchAll(PDO::FETCH_ASSOC);

        foreach ($recentData as $row) {
            $sql = $this->db->prepare('SELECT sex, age, eventData.ID, entries 
                                       FROM eventData 
                                       LEFT OUTER JOIN categories ON eventData.catID = categories.ID 
                                       WHERE dateID = :dateID AND eventID = :eventID 
                                       ORDER BY sex, age ASC');
            $sql->bindValue(":dateID", $row['ID']);
            $sql->bindValue(":eventID", $row['eventID']);
            $sql->execute();
            $catData = $sql->fetchAll(PDO::FETCH_ASSOC);

            $catCount = 1;
            $catsCount = 0;
            
            $row['category'.$catCount] = $catData[0]['sex'];
            foreach ($catData as $cat) {
                if ($cat['sex'] != $row['category'.$catCount]) { 
                    while ($catsCount < 5) {
                        $row['category'.$catCount.'age'.$catsCount] = '';
                        $row['category'.$catCount.'ID'.$catsCount++] = '';
                    }
                    $catsCount = 0;
                    $row['category'.++$catCount] = $cat['sex'];
                } 
                $row['category'.$catCount.'age'.$catsCount] = $cat['age'];
                $row['category'.$catCount.'ID'.$catsCount++] = $cat['ID'];
            }

            while ($catsCount < 5) {
                $row['category'.$catCount.'age'.$catsCount] = '';
                $row['category'.$catCount.'ID'.$catsCount++] = '';
            }

            if ($catCount < 2) {
                $row['category2'] = '';
                $row['category2age0'] = '';
                $row['category2age1'] = '';
                $row['category2age2'] = '';
                $row['category2age3'] = '';
                $row['category2age4'] = '';
            }

            $res['data'][] = $row;
        }
        return $res; 
    }

    public function getForthcoming() {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT date, eventName, a1, a2, a3, a4, a5, a6, a7, a8, a9, a10, a11, infoLink, region, 
                                    DATE_FORMAT(date,\'%D\') AS date1, DATE_FORMAT(date,\'%M\') AS date2, selected, tbc, 
                                    locale, DATE_FORMAT(date,\'%Y\') AS date3, date AS fullDate
                                   FROM forthcoming 
                                   WHERE date > NOW() 
                                   ORDER BY date ASC');
        $sql->execute();
        $forthData = $sql->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($forthData as $row) {
            $row['blank'] = '';

            $cats = ['cat1', 'cat2', 'cat3', 'cat4', 'cat5'];
            $catc = 0;
            
            if ($row['a1'] == 1) $row[$cats[$catc++]] = 'U9';
            if ($row['a2'] == 1) $row[$cats[$catc++]] = 'U10';
            if ($row['a3'] == 1) $row[$cats[$catc++]] = 'U11';
            if ($row['a4'] == 1) $row[$cats[$catc++]] = 'U12';
            if ($row['a5'] == 1) $row[$cats[$catc++]] = 'U13';
            if ($row['a6'] == 1) $row[$cats[$catc++]] = 'U14';
            if ($row['a7'] == 1) $row[$cats[$catc++]] = 'U15';
            if ($row['a8'] == 1) $row[$cats[$catc++]] = 'U16';
            if ($row['a9'] == 1) $row[$cats[$catc++]] = 'U17';
            if ($row['a10'] == 1) $row[$cats[$catc++]] = 'U18';
            if ($row['a11'] == 1) $row[$cats[$catc++]] = 'U20';

            for ($c = $catc; $c < 5; $c++) {
                $row[$cats[$c]] = '';
            }
                
            $res['data'][] = $row;
        }

        return $res; 

    }

    private function placeSuffix($number)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
            else return $number. $ends[$number % 10];
    }

}
