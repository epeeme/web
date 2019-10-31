<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class fencer extends DB {

    public function getFinishingPositions($data) {        
        $sql = $this->db->prepare('SELECT SUM(CASE WHEN eventPosition = 1 THEN 1 ELSE 0 END) AS PC, \'1st\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition = 2 THEN 1 ELSE 0 END) AS PC, \'2nd\' AS Position  FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition = 3 THEN 1 ELSE 0 END) AS PC, \'3rd\' AS Position  FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition > 3 AND eventPosition < 9 THEN 1 ELSE 0 END) AS PC, \'L8\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition > 8 AND eventPosition < 17 THEN 1 ELSE 0 END) AS PC, \'L16\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition > 16 AND eventPosition < 33 THEN 1 ELSE 0 END) AS PC, \'L32\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition  > 32 AND eventPosition < 65 THEN 1 ELSE 0 END) AS PC, \'L64\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition  > 64 AND eventPosition < 129 THEN 1 ELSE 0 END) AS PC, \'L128\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition  > 128 AND eventPosition < 257 THEN 1 ELSE 0 END) AS PC, \'L256\' AS Position FROM results WHERE fencerID = :fencerID
                                    UNION ALL SELECT SUM(CASE WHEN eventPosition  > 256 AND eventPosition < 513 THEN 1 ELSE 0 END) AS PC, \'L512\' AS Position  FROM results WHERE fencerID = :fencerID');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getNumberOfComps($data) {
        $sqlBuild = [];
        $yearIndex = 2005;
        $endYear = date("Y");
        do {
            $sqlBuild[] = "SELECT '".$yearIndex."' AS year, '".substr($yearIndex, 2, 2)."' AS yearShort, count(*) AS cCount
                            FROM results 
                            LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                            WHERE fencerID = :fencerID  AND year = ".$yearIndex++;
        } while ($yearIndex <= $endYear);

        $sqlQuery = implode(" UNION ALL ", $sqlBuild);
        $sql = $this->db->prepare($sqlQuery);
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventCounts($data) {
        return ["LPJS" => $this->countLPJSevents($data),
                "Elite" => $this->countEliteevents($data),
                "ENG" => $this->countENGevents($data),
                "GBR" => $this->countGBRevents($data)];
    }

    private function countLPJSevents($data) {
        $sql = $this->db->prepare('SELECT count(results.ID) FROM results 
                                   INNER JOIN events ON events.ID = results.eventID
                                   WHERE fencerID = :fencerID AND events.eventType = \'LPJS\'');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    private function countEliteevents($data) {
        $sql = $this->db->prepare('SELECT count(results.ID) FROM results 
                                   INNER JOIN events ON events.ID = results.eventID
                                   WHERE fencerID = :fencerID AND events.eventType = \'Elite\'');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    private function countENGevents($data) {
        $sql = $this->db->prepare('SELECT count(results.ID) 
                                   FROM results 
                                   WHERE fencerID = :fencerID AND results.eventCat < 19');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    private function countGBRevents($data) {
        $sql = $this->db->prepare('SELECT count(results.ID) 
                                   FROM results 
                                   WHERE fencerID = :fencerID AND (results.eventCat = 25 OR results.eventCat = 26)');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    public function getFencerProfile($data) {
        $sql = $this->db->prepare('SELECT fencerFullname, country, yob 
                                   FROM fencers 
                                   WHERE ID = :fencerID');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        $row = $sql->fetch(PDO::FETCH_ASSOC);
        $row2 = $this->getFencerRegion($data);
        return [$row, $row2];
    }

    public function getFencerMedals($data) {
        $sql = $this->db->prepare('SELECT 
                                    SUM(CASE WHEN eventPosition = 1 THEN 1 ELSE 0 END) AS Gold,
                                    SUM(CASE WHEN eventPosition = 2 THEN 1 ELSE 0 END) AS Silver,
                                    SUM(CASE WHEN eventPosition = 3 THEN 1 ELSE 0 END) AS Bronze
                                   FROM results 
                                   WHERE fencerID = :fencerID AND eventPosition <= 3');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getFencerRegion($data) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                    FROM results 
                                    INNER JOIN clubs ON results.fencerClubID = clubs.ID 
                                    INNER JOIN eventDates ON results.dateID = eventDates.ID 
                                    WHERE fencerID = :fencerID AND region = 1
                                    ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getFencerClub($data) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                    FROM results 
                                    INNER JOIN clubs ON results.fencerClubID = clubs.ID 
                                    INNER JOIN eventDates ON results.dateID = eventDates.ID 
                                    WHERE fencerID = :fencerID AND region = 0 AND cty = 0 AND uni = 0 AND sch = 0 AND clubs.ID <> 261 
                                    ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        if (empty($res)) {
            $sql = $this->db->prepare('SELECT DISTINCT clubName, clubs.ID, fullDate 
                                        FROM results 
                                        INNER JOIN clubs ON results.fencerClubID = clubs.ID 
                                        INNER JOIN eventDates ON results.dateID = eventDates.ID 
                                        WHERE fencerID = :fencerID
                                        ORDER BY eventDates.fullDate DESC LIMIT 0,1');
            $sql->bindValue(":fencerID", $data['fencerID']);
            $sql->execute();
            $res = $sql->fetch(PDO::FETCH_ASSOC);
        }
        return $res;
    }

    public function getHistory($data) {
        $res['data'] = [];

        $sql = $this->db->prepare('SELECT DISTINCT year, DATE_FORMAT(fullDate,\'%d-%m-%Y\') AS fDate, eventType,  eventName,  age,  eventPosition, entries, lpjsPoints, NIFF, NIFFvalue,
                                    CASE 
                                        WHEN eventPosition <= ((entries/2)+(entries/4)) && NIFF = 1 THEN FLOOR(m3.multiplier * NIFFValue) 
                                    ELSE 0 END AS RankingPoints1,
                                    CASE 
                                        WHEN eventPosition <= ((entries/2)+(entries/4)) && NIFF = 1 THEN FLOOR(m2.multiplier * NIFFValue) 
                                    ELSE 0 END AS RankingPoints2,
                                    CASE 
                                        WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 0 && outOfAge = 0 THEN FLOOR(m1.multiplier * NIFFValue * entries) 
                                        WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 1 && outOfAge = 0 THEN FLOOR(m1.multiplier_rep * NIFFValue * entries) 
                                    ELSE 0 END AS RankingPoints3,
                                    CASE WHEN eventPosition > (entries/2) || NIFF = 0 THEN
                                        CASE WHEN NIFFValue > 0 THEN FLOOR(m1.multiplier * NIFFValue) ELSE FLOOR(m1.multiplier * 1) END
                                    END AS RankingPoints4,
                                    CASE 
                                        WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 0 && outOfAge = 0 THEN FLOOR(m1.multiplier * NIFFValue) 
                                        WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 1 && outOfAge = 0 THEN FLOOR(m1.multiplier_rep * NIFFValue) 
                                    ELSE 0 END AS RankingPoints5,
                                    CASE WHEN eventPosition > (entries/2) || NIFF = 0 THEN
                                        CASE WHEN NIFFValue > 0 THEN FLOOR(m1.multiplier * NIFFValue) ELSE FLOOR(m1.multiplier * 1) END
                                    END AS RankingPoints6,
                                    repechage, categories.ID AS catID, eventDates.ID AS dateID, results.eventID, sex, elitePoints, results.ID, nominated, 
                                    eventData.ID AS eventDataID, events.ID AS eventID, Rank1, Rank2
                                FROM results
                                INNER JOIN events ON events.ID = results.eventID
                                INNER JOIN categories ON categories.ID = results.eventCat    
                                INNER JOIN eventDates ON eventDates.ID = results.dateID 
                                INNER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID AND eventData.CatID = results.eventCat
                                LEFT JOIN multipliers m1 ON m1.position = results.eventPosition
                                LEFT JOIN multipliers_snr_2013 m2 ON m2.position = results.eventPosition
                                LEFT JOIN multipliers_snr m3 ON m3.position = results.eventPosition
                                WHERE fencerID =:fencerID
                                ORDER BY fullDate DESC, age');
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();

        $fencerData = $sql->fetchAll(PDO::FETCH_ASSOC);      
        
        foreach ($fencerData as $row) {
            $row['eventPositionDisplay'] = $this->placeSuffix($row['eventPosition']);
            $row['Rank1Display'] = $row['Rank1'] > 0 ? $this->placeSuffix($row['Rank1']) : '-';
            $row['Rank2Display'] = $row['Rank2'] > 0 ? $this->placeSuffix($row['Rank2']) : '-';
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
