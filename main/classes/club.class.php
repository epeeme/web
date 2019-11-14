<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class club extends DB {
    
    public function isCountry($data) {
        $sql = $this->db->prepare('SELECT cty FROM clubs WHERE ID = :cID');
        $sql->bindValue(":cID", $data['cID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);      
    }

    public function getClubFencers($data) {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT fencerFirstname, fencerSurname, fencerFullname, fencerID, yob, fencers.country, 
                                          DATE_FORMAT(MAX(fullDate),\'%d-%m-%Y\') AS LastRep, DATE_FORMAT(MIN(fullDate),\'%d-%m-%Y\') AS FirstRep, 
                                          count(results.dateID) AS TimesRep
                                   FROM results 
                                   INNER JOIN eventDates ON eventDates.ID = results.dateID
                                   INNER JOIN clubs ON clubs.ID = fencerClubID 
                                   INNER JOIN fencers ON fencers.ID = fencerID 
                                   WHERE fencerClubID = :cID
                                   GROUP BY fencerFullname');
        $sql->bindValue(":cID", $data['cID']);
        $sql->execute();

        $clubData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        foreach ($clubData as $row) {
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
