<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class search extends DB {

    public function getFencers($data) {    
        $sql = $this->db->prepare('SELECT ID, fencerFullname, country, yob 
                                   FROM fencers 
                                   WHERE fencerFullname LIKE :qs 
                                   ORDER BY fencerFullname ASC LIMIT 0, 5');
        $sql->bindValue(":qs", $data['qs'].'%');
        $sql->execute();
        $res = $sql->fetchAll(PDO::FETCH_ASSOC);
        if (count($res) < 5) { 
            $sql = $this->db->prepare('SELECT ID, fencerFullname, country, yob 
                                       FROM fencers 
                                       WHERE fencerFullname LIKE :qs 
                                       ORDER BY fencerFullname ASC LIMIT 0, 5');
            $sql->bindValue(":qs", '%'.$data['qs']);
            $sql->execute();
            $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            if (count($res) < 5) { 
                $sql = $this->db->prepare('SELECT ID, fencerFullname, country, yob 
                                           FROM fencers 
                                           WHERE fencerFullname LIKE :qs 
                                           ORDER BY fencerFullname ASC LIMIT 0, 5');
                $sql->bindValue(":qs", '%'.$data['qs'].'%');
                $sql->execute();
                $res = $sql->fetchAll(PDO::FETCH_ASSOC);
            }
        }
      
        $ff = [];
        if (count($res) > 0) { 
            foreach ($res as $fencer) {
                $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                           FROM results 
                                           LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                           LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                           WHERE fencerID = :fencerID AND region = 0 AND cty = 0 AND uni = 0 AND sch = 0 AND clubs.ID <> 261 
                                           ORDER BY eventDates.fullDate DESC LIMIT 0,1');
                $sql->bindValue(":fencerID", $fencer['ID']);
                $sql->execute();
                $resClub = $sql->fetch(PDO::FETCH_ASSOC);
                if (!isset($resClub['clubName'])) {
                    $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                               FROM results 
                                               LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                               LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                               WHERE fencerID = :fencerID AND region = 0 AND (cty = 1 OR uni = 1 OR sch = 1) 
                                               ORDER BY eventDates.fullDate DESC LIMIT 0,1');
                    $sql->bindValue(":fencerID", $fencer['ID']);
                    $sql->execute();
                    $resClub = $sql->fetch(PDO::FETCH_ASSOC);
                    if (!isset($resClub['clubName'])) {
                        $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                                    FROM results 
                                                    LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                                    LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                                    WHERE fencerID = :fencerID AND region = 1 ORDER BY eventDates.fullDate DESC LIMIT 0,1');
                        $sql->bindValue(":fencerID", $fencer['ID']);
                        $sql->execute();
                        $resClub = $sql->fetch(PDO::FETCH_ASSOC);
                    }
                }
                $f = ['id' => $fencer['ID'], 
                      'value' => $fencer['fencerFullname'], 
                      'club' => $resClub['clubName'] !== null ? $resClub['clubName'] : '', 
                      'cty' => $fencer['country'] !== null && $fencer['country'] <> '' ? $fencer['country'] : 'AAA', 
                      'yob' => $fencer['yob'] !== null ? $fencer['yob'] : ' '];
                $ff[] = $f;
            }
        }
        return $ff;
    }

    public function getFencersList($data) {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT fencers.ID, fencerFirstname, fencerSurname, yob, clubName, fencers.country, BFA
                                   FROM fencers
                                   LEFT JOIN clubs ON clubs.ID = (SELECT clubs.ID FROM results AS r 
                                                                  INNER JOIN clubs ON r.fencerClubID = clubs.ID 
                                                                  INNER JOIN eventDates ON r.dateID = eventDates.ID 
                                                                  WHERE fencers.ID = r.fencerID AND region = 0 AND cty = 0
                                                                  ORDER BY eventDates.fullDate DESC LIMIT 0,1)
                                   WHERE fencerFullname LIKE :qs
                                   ORDER BY fencerFullname ASC');
        $sql->bindValue(":qs", '%'.$data['qs'].'%');
        $sql->execute();

        $searchData = $sql->fetchAll(PDO::FETCH_ASSOC);
        foreach ($searchData as $row) {
            $row['blank'] = '';
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
