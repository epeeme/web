<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";
require_once "cadet.class.php";
require_once "junior.class.php";

class event extends DB {
    
    protected $config = null;
    
    public function __construct($cfg) {
        $this->config = $cfg;
        parent::__construct($this->config);
    }
    
    public function getNumberOfCategories($data) {
        $sql = $this->db->prepare('SELECT DISTINCT catID, categories.sex, age
                                   FROM eventData 
                                   INNER JOIN categories ON categories.ID = eventData.catID
                                   WHERE dateID = :dateID AND eventID = :eventID
                                   ORDER BY sex, age');

        $sql->bindValue(":dateID", $data['dateID']);
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventDetails($data) {
        $sql = $this->db->prepare('SELECT eventType, eventName, DATE_FORMAT(fullDate,\'%D %M %Y\') AS eventDate, 
                                          age, sex, NIFFvalue, year, nominated, catID, eventData.eventID, 
                                          eventData.dateID
                                   FROM eventData 
                                   INNER JOIN eventDates ON eventDates.ID = eventData.dateID 
                                   INNER JOIN events ON events.ID = eventData.eventID 
                                   INNER JOIN categories ON categories.ID = eventData.catID
                                   WHERE eventData.dateID = :dateID AND eventData.eventID = :eventID');
        $sql->bindValue(":dateID", $data['dateID']);
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getCatNIF($data) {
      $sql = $this->db->prepare('SELECT NIFFvalue
                                 FROM eventData 
                                 WHERE dateID = :dateID AND eventID = :eventID AND catID = :catID');
      $sql->bindValue(":dateID", $data['dateID']);
      $sql->bindValue(":eventID", $data['eventID']);
      $sql->bindValue(":catID", $data['catID']);
      $sql->execute();
      return $sql->fetch(PDO::FETCH_COLUMN);
  }

  public function getResult($data) {
        $res['data'] = [];

        $sql = $this->db->prepare('SELECT DISTINCT eventPosition, fencerFirstname, fencerSurname, clubName, fencers.country, lpjsPoints, NIFF, NIFFvalue, 
                                          fencerID, fencerClubID, elitePoints, NIF1, yob, efr, flagName, categories.sex, age,
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
                                          entries, repechage, nominated, eventType, catID
                                    FROM eventData e 
                                    INNER JOIN results r ON r.eventID = e.eventID AND r.dateID = e.dateID AND r.eventCat = e.catID
                                    INNER JOIN fencers ON fencers.ID = r.fencerID 
                                    INNER JOIN clubs ON clubs.ID = r.fencerClubID 
                                    INNER JOIN events ON events.ID = e.eventID
                                    LEFT JOIN categories ON categories.ID = e.catID
                                    LEFT JOIN flags ON flags.clubID = clubs.ID
                                    LEFT JOIN multipliers m1 ON m1.position = r.eventPosition
                                    LEFT JOIN multipliers_snr_2013 m2 ON m2.position = r.eventPosition
                                    LEFT JOIN multipliers_snr m3 ON m3.position = r.eventPosition
                                    WHERE e.dateID = :dateID AND e.eventID = :eventID
                                    ORDER BY categories.sex, age, r.eventPosition ASC');
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->bindValue(":dateID", $data['dateID']);
        $sql->execute();

        $resultData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        if (($resultData[0]['nominated'] == 2) || ($resultData[0]['nominated'] == 3)) $cadet = new cadet($this->config);
        if ($resultData[0]['nominated'] == 4) $junior = new junior();
        
        foreach ($resultData as $row) {
            $row['eventPositionDisplay'] = $this->placeSuffix($row['eventPosition']);
            switch ($row['nominated']) {
                case 2 : { 
                    $row['cadetRankingPoints'] =  $cadet->getUKMultipler($row['eventPosition'], $row['entries'], $row['eventType'], $row['repechage']);
                    $row['juniorRankingPoints'] = 0;
                    break;
                }
                case 3 : { 
                    $row['cadetRankingPoints'] = $cadet->getUKMultipler($row['eventPosition'], $row['entries'], $row['eventType'], $row['repechage']);
                    $row['juniorRankingPoints'] = $junior->getUKMultipler($row['eventPosition'], $row['entries'], $row['eventType'], $row['repechage']);
                    break;
                }
                case 4 : { 
                    $row['juniorRankingPoints'] =  $junior->getUKMultipler($row['eventPosition'], $row['entries'], $row['eventType'], $row['repechage']);
                    $row['cadetRankingPoints'] = 0;
                    break;
                }
                default : {
                    $row['cadetRankingPoints'] = 0;
                    $row['juniorRankingPoints'] = 0;
                }
            }
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
