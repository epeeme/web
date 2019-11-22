<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class club extends DB {
    
    public function getNumberOfComps($data) {
        $sqlBuild = [];
        $yearIndex = 2005;
        $endYear = date("Y");
        do {
            $sqlBuild[] = "SELECT '".$yearIndex."' AS year, '".substr($yearIndex, 2, 2)."' AS yearShort, count(*) AS cCount, count(DISTINCT fencerID) AS cCount2
                           FROM results 
                           LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                           WHERE fencerClubID = :clubID AND year = ".$yearIndex++;
        } while ($yearIndex <= $endYear);

        $sqlQuery = implode(" UNION ALL ", $sqlBuild);
        $sql = $this->db->prepare($sqlQuery);
        $sql->bindValue(":clubID", $data['cID']);
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function isCountry($data) {
        $sql = $this->db->prepare('SELECT cty FROM clubs WHERE ID = :cID');
        $sql->bindValue(":cID", $data['cID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);      
    }

    public function getClubData($data) {
        $sql = $this->db->prepare('SELECT clubName, Facebook, Twitter, Web
                                   FROM clubs
                                   WHERE id = :clubID');
        $sql->bindValue(":clubID", $data['cID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getFencerMedals($fencerID, $clubID) {
        $sql = $this->db->prepare('SELECT 
                                    SUM(CASE WHEN eventPosition = 1 THEN 1 ELSE 0 END) AS Gold,
                                    SUM(CASE WHEN eventPosition = 2 THEN 1 ELSE 0 END) AS Silver,
                                    SUM(CASE WHEN eventPosition = 3 THEN 1 ELSE 0 END) AS Bronze
                                   FROM results 
                                   WHERE fencerID = :fencerID AND fencerClubID = :clubID AND eventPosition <= 3');
        $sql->bindValue(":fencerID", $fencerID);
        $sql->bindValue(":clubID", $clubID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getClubFencers($data) {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT fencerFirstname, fencerSurname, fencerFullname, fencerID, yob, fencers.country, 
                                          DATE_FORMAT(MAX(fullDate),\'%d-%m-%Y\') AS LastRep, DATE_FORMAT(MIN(fullDate),\'%d-%m-%Y\') AS FirstRep, 
                                          count(results.dateID) AS TimesRep, region, cty, sch, uni
                                   FROM results 
                                   INNER JOIN eventDates ON eventDates.ID = results.dateID
                                   INNER JOIN clubs ON clubs.ID = fencerClubID 
                                   INNER JOIN fencers ON fencers.ID = fencerID 
                                   WHERE fencerClubID = :cID
                                   GROUP BY fencerFullname');
        $sql->bindValue(":cID", $data['cID']);
        $sql->execute();

        $clubData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $sql = $this->db->prepare('SELECT region, cty, sch, uni
                                   FROM clubs
                                   WHERE ID = :cID');
        $sql->bindValue(":cID", $data['cID']);
        $sql->execute();

        $clubInfo = $sql->fetch(PDO::FETCH_ASSOC);      

        if ($clubInfo['region'] == 1) $clubType = ' AND region = 1';
            else if ($clubInfo['cty'] == 1) $clubType = ' AND cty = 1';
            else if ($clubInfo['sch'] == 1) $clubType = ' AND sch = 1';
            else if ($clubInfo['uni'] == 1) $clubType = ' AND uni = 1';
            else $clubType = 'AND (region = 0 AND cty = 0 AND sch = 0 AND uni = 0)';

        foreach ($clubData as $row) {
            
            $sql = $this->db->prepare('SELECT fencerClubID, fullDate
                                       FROM results 
                                       INNER JOIN eventDates ON results.dateID = eventDates.ID 
                                       INNER JOIN clubs ON clubs.ID = fencerClubID 
                                       WHERE fencerID = :fencerID '.$clubType.'
                                       ORDER BY eventDates.fullDate DESC LIMIT 0,1');
            $sql->bindValue(":fencerID", $row['fencerID']);
            $sql->execute();

            $clubsData = $sql->fetch(PDO::FETCH_ASSOC);      
            
            $row['Active'] = $clubsData['fencerClubID'] == $data['cID'] ? true : false;

            $medals = $this->getFencerMedals($row['fencerID'], $data['cID']);

            $row['Gold'] = $medals['Gold'] != '' ? $medals['Gold'] : 0;
            $row['Silver'] = $medals['Silver'] != '' ? $medals['Silver'] : 0;
            $row['Bronze'] = $medals['Bronze'] != '' ? $medals['Bronze'] : 0;

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
