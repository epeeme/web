<?php

require_once "DB.class.php";

class junior extends DB {
    
    private $seasonStart = null;
    private $seasonEnd = null;

    const JWC_DATA_START = 2005;
    
    // ID codes for major championships
    const EUROS = 199;
    const WORLDS = 198;
    
    public function jwcCountryList($data) {
        $sql = $this->db->prepare('SELECT DISTINCT clubs.ID, clubName
                                   FROM results 
                                   INNER JOIN events ON events.ID = results.eventID     
                                   INNER JOIN clubs ON clubs.ID = results.fencerClubID
                                   WHERE eventType = \'JWC\' AND clubs.ID <> 415
                                   ORDER BY clubName ASC');       
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function jwcHistory($data) {
        
        $res['data'] = [];

        $seasonEnd = self::JWC_DATA_START;
        if ($data['season'] == '-1') {
            $seasonTracker = (int)date('Y');
        } else {
            $seasonTracker = (int)$data['season'];
            $seasonEnd = $seasonTracker - 3;
        }

        $seasonSQL = '';
        $params = null;

        while ($seasonTracker >= $seasonEnd) {

            $this->setSeasonStartEnd($seasonTracker);
            
            $seasonSQL .= 'SUM(CASE WHEN fullDate > ? AND fullDate <= ? THEN 1 END) AS Season_'.$seasonTracker.'_Count,
                           SUM(CASE WHEN fullDate > ? AND fullDate <= ? THEN (100 / entries) * eventPosition END) AS Season_'.$seasonTracker.'_Total,
                           SUM(CASE WHEN results.eventID = '.self::EUROS.' AND (fullDate > ? AND fullDate <= ?) THEN 1 ELSE 0 END) AS Season_'.$seasonTracker.'_Euros,
                           SUM(CASE WHEN results.eventID = '.self::WORLDS.' AND (fullDate > ? AND fullDate <= ?) THEN 1 ELSE 0 END) AS Season_'.$seasonTracker.'_Worlds,';
           
            $params[] = $this->seasonStart; $params[] = $this->seasonEnd;    
            $params[] = $this->seasonStart; $params[] = $this->seasonEnd;    
            $params[] = $this->seasonStart; $params[] = $this->seasonEnd;    
            $params[] = $this->seasonStart; $params[] = $this->seasonEnd;    

            $seasonTracker -=1;
        };

        $params[] = $data['catID'];
        $params[] = $data['catID'];    
        $params[] = $data['country'];    
        
        $sql = $this->db->prepare('SELECT '.$seasonSQL.' results.fencerID, fencerFirstname, fencerSurname, yob, efr
                                   FROM results 
                                   INNER JOIN eventData ON eventData.eventID = results.eventID AND eventData.dateID = results.dateID
                                   INNER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   INNER JOIN fencers ON fencers.ID = results.fencerID 
                                   INNER JOIN events ON events.ID = results.eventID     
                                   WHERE eventData.catID = ? AND results.eventCat = ? AND fencerClubID = ? AND eventType = \'JWC\' AND eventPosition <> 9999
                                   GROUP BY results.fencerID');

        $sql->execute($params);

        $fencerData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $seasonTracker = isset($data['season']) ? (int)$data['season'] : (int)date('Y');

        foreach ($fencerData as $row) {
            
            $seasonTrackerTemp = $seasonTracker;
            $overallCount = 0;
            $overallTotal = 0;
            while ($seasonTrackerTemp >= $seasonEnd) {
                $overallCount += $row['Season_'.$seasonTrackerTemp.'_Count'];
                $overallTotal += $row['Season_'.$seasonTrackerTemp.'_Total'];
                $seasonTrackerTemp -= 1;
            }
            
            $row['Overall_Count'] = $overallCount;
            $row['Overall_Total'] = $overallTotal;
            $row['blank'] = '';
            if ($data['season'] == '-1') {
                $res['data'][] = $row;
            } else if ($row['Season_'.$data['season'].'_Count']) {
                $res['data'][] = $row;

            }
        }

        return $res; 

    }
    
    public function jwcEventHistory($data) {
        
        $res['data'] = [];

        $params = null;

        if ($data['season'] > 0) {
            $this->setSeasonStartEnd($data['season']);
            $params[] = $this->seasonStart;
            $params[] = $this->seasonEnd;    
        } else {
            $params[] = self::JWC_DATA_START;
            $params[] = date('Y-m-d');    
        }

        $params[] = $data['catID'];
        $params[] = $data['catID'];    
        $params[] = $data['country'];    
            
        $sql = $this->db->prepare('SELECT events.eventName,
                                          CAST(AVG(entries) AS Integer) AS entries, CAST(AVG(eventPosition) AS Integer) AS position, 
                                          SUM(CASE WHEN eventPosition = 1 THEN 1 ELSE 0 END) AS first, 
                                          SUM(CASE WHEN eventPosition = 2 THEN 1 ELSE 0 END) AS second, 
                                          SUM(CASE WHEN eventPosition = 3 THEN 1 ELSE 0 END) AS third, 
                                          SUM(CASE WHEN eventPosition > 3 AND eventPosition < 9 THEN 1 ELSE 0 END) AS last8, 
                                          SUM(CASE WHEN eventPosition > 8 AND eventPosition < 17 THEN 1 ELSE 0 END) AS last16, 
                                          SUM(CASE WHEN eventPosition > 16 AND eventPosition < 33 THEN 1 ELSE 0 END) AS last32, 
                                          SUM(CASE WHEN eventPosition  > 32 AND eventPosition < 65 THEN 1 ELSE 0 END) AS last64, 
                                          SUM(CASE WHEN eventPosition  > 64 AND eventPosition < 129 THEN 1 ELSE 0 END) AS last128, 
                                          SUM(CASE WHEN eventPosition  > 128 AND eventPosition < 257 THEN 1 ELSE 0 END) AS last256, 
                                          SUM(CASE WHEN eventPosition  > 256 AND eventPosition < 513 THEN 1 ELSE 0 END) AS last512
                                  FROM results 
                                  LEFT OUTER JOIN events ON events.ID  = results.eventID 
                                  LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID 
                                  LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                  WHERE (fullDate > ? and fullDate <= ?) AND eventData.catID = ? AND results.eventCat = ? AND eventType = \'JWC\' AND fencerClubID = ?
                                  GROUP BY events.eventName');
                
        $sql->execute($params);
        $eventData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $totalEntries = 0;

        $avgPos = [];

        foreach ($eventData as $row) {                        
            $totalEntries += $row['entries'];
            array_push($avgPos, ((100 / $row['entries']) * $row['position']));
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        $sql = $this->db->prepare('SELECT CAST(AVG(eventPosition) AS Integer) AS averagePosition
                                   FROM results 
                                   LEFT OUTER JOIN events ON events.ID  = results.eventID 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID 
                                   LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   WHERE (fullDate > ? and fullDate <= ?) AND eventData.catID = ? AND results.eventCat = ? AND eventType = \'JWC\' AND fencerClubID = ?');                
        $sql->execute($params);
        $ap = $sql->fetch(PDO::FETCH_ASSOC);      
                    
        $res['ap'] =  $ap['averagePosition'];
        $res['app'] =  array_sum($avgPos) / count($eventData);
        $res['te'] =  $totalEntries;

        return $res; 

    }


    public function getUKMultipler($place, $entries, $eventType, $rep = false) {

        // Base points

        $points = 0;

        if (($place <= floor(($entries/ 100) * 80)) && ($place <= 64)) {

            if ($place == 1) $points = 40;
                else if ($place == 2) $points = 36; 
                else if ($place == 3) $points = 32; 
                else if ($place == 5) $points = 27; 
                else if ($place == 6) $points = 26; 
                else if ($place == 7) $points = 26; 
                else if ($place == 8) $points = 24; 
                else if ($place >= 9 && $place <= 16) $points = 16; 
                else if ($place >= 17 && $place <= 32) $points = 8; 
                else if ($place >= 33 && $place <= 64) $points = 4; 

            // Additional points for repercharge

            if ($rep === 1) {
                if ($place >= 9 && $place <= 12) $points = 20; 
                    else if ($place >= 17 && $place <= 24) $points = 12; 
            }

            // Adjust for Nationals

            if ($eventType == 'BJC') $points = $points * 1.2; // Nationals adjustment
            if ($eventType == 'Open') $points = $points * 1.5; // Nationals adjustment

        }
        return $points;
    }

    private function setSeasonStartEnd($season) {

        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = '.self::WORLDS.' AND year = :year');
        $sql->bindValue(":year", $season);
        $sql->execute();
        $this->seasonStart = $sql->fetch(PDO::FETCH_COLUMN);
        if (empty($this->seasonStart)) $this->seasonStart = $season.'-04-31';
        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = '.self::WORLDS.' AND year = :year');
        $sql->bindValue(":year", ($season + 1));
        $sql->execute();
        $this->seasonEnd = $sql->fetch(PDO::FETCH_COLUMN);
        if (empty($this->seasonEnd)) $this->seasonEnd = ($season+1).'-04-31';

    }

}

