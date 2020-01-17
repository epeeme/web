<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class cadet extends DB {

    private $seasonStart = null;
    private $seasonEnd = null;

    // As Cadet Rankings are made up of BRC and EFC events, a pseudo series needs to be created
    private $cadetSQL = '(((eventType = \'BRC\' OR eventType = \'BCC\' OR eventType = \'BSC\') AND nominated IN (1,2,3)) OR (eventType = \'EFC\' AND nominated IN (1,2,3)))';

    // Include only fencers from certain countries in the ranking list
    private $countries = array('\'ENG\'', '\'SCO\'', '\'WAL\'', '\'GBR\'', '\'NIR\'', '\'GUE\'');

    // ID codes for major championships
    const EUROS = 163;
    const WORLDS = 197;
    
    const EFC_DATA_START = 2005;

    private function setSeasonStartEnd($season) {

        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = 197 AND year = :year');
        $sql->bindValue(":year", $season);
        $sql->execute();
        $this->seasonStart = $sql->fetch(PDO::FETCH_COLUMN);
        if (empty($this->seasonStart)) $this->seasonStart = $season.'-04-31';
        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = 197 AND year = :year');
        $sql->bindValue(":year", ($season + 1));
        $sql->execute();
        $this->seasonEnd = $sql->fetch(PDO::FETCH_COLUMN);
        if (empty($this->seasonEnd)) $this->seasonEnd = ($season+1).'-04-31';

    }

    public function getSeasonSize($data) {
        
        $this->setSeasonStartEnd($data['season']);

        $sql = $this->db->prepare('SELECT events.ID
                                   FROM events 
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   WHERE '.$this->cadetSQL.' AND eventData.catID IN (6,2) AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd)
                                   GROUP BY events.ID, eventDates.ID');
        $sql->bindValue(":seasonStart", $this->seasonStart);
        $sql->bindValue(":seasonEnd", $this->seasonEnd);
        $sql->execute();

        return count($sql->fetchAll(PDO::FETCH_ASSOC));

    }

    public function getSeasonResults($data) {

        $this->setSeasonStartEnd($data['season']);

        $res['data'] = [];
        $sql = $this->db->prepare('SELECT results.fencerID, fencerFirstname, fencerSurname, yob, clubName, efr
                                   FROM results 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND eventData.dateID = results.dateID
                                   LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   LEFT OUTER JOIN fencers ON fencers.ID = results.fencerID 
                                   LEFT OUTER JOIN events ON events.ID = results.eventID       
                                   LEFT JOIN clubs ON clubs.ID = (SELECT clubs.ID FROM results AS r 
                                                                  INNER JOIN clubs ON r.fencerClubID = clubs.ID 
                                                                  INNER JOIN eventDates ON r.dateID = eventDates.ID 
                                                                  WHERE fencers.ID = r.fencerID AND region = 0 AND cty = 0
                                                                  ORDER BY eventDates.fullDate DESC LIMIT 0,1)
                                   WHERE '.$this->cadetSQL.'
                                         AND eventData.catID IN (6,2) AND eventData.catID = :catID AND results.eventCat = :catID2
                                         AND fencers.country IN ('.implode(',', $this->countries).')
                                         AND (fullDate >= :seasonStart and fullDate <= :seasonEnd)
                                   GROUP BY results.fencerID');
        $sql->bindValue(":catID", $data['catID']);
        $sql->bindValue(":catID2", $data['catID']);
        $sql->bindValue(":seasonStart", $this->seasonStart);
        $sql->bindValue(":seasonEnd", $this->seasonEnd);
        $sql->execute();

        $fencerData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $points = null;
        $position = null;        
        $runningPosition = 0;        

        $seriesData = $this->getSeriesBreakdown($data);        

        foreach ($fencerData as $row) {
           $threeHighestDomestic = [];
           $threeHighestInternational = [];
           $Domestic = [];
           $International = [];

           $row['cadetNationals'] = 9999;

           for ($i=0; $i<count($seriesData); $i++) { 

                $cadetPoints = $this->getCadetPoints(['fencerID' => $row['fencerID'], 
                                                      'dateID' => $seriesData[$i]['ID'], 
                                                      'eventID' => $seriesData[$i]['eventID'],
                                                      'eventType' => $seriesData[$i]['eventType'],
                                                      'coef' => $seriesData[$i]['coef'],
                                                      'f128' => $seriesData[$i]['f128'],
                                                      'entries' => $seriesData[$i]['entries']]);
                $row['e'.$i.'_points'] = $cadetPoints['points'];
                $row['e'.$i.'_place'] = (int)$cadetPoints['place'];
                $row['e'.$i.'_placeSuffix'] = $row['e'.$i.'_place'] > 0 ? $this->placeSuffix($cadetPoints['place']) : '';
                $row['e'.$i.'_eventName'] = $seriesData[$i]['eventName'];
                $row['e'.$i.'_eventType'] = $seriesData[$i]['eventType'];
                $row['e'.$i.'_eventDate'] = $seriesData[$i]['fullDateReal'];
                if ($row['e'.$i.'_place'] > 0) {
                    switch ($seriesData[$i]['eventType']) {
                        case 'BRC' :
                        case 'BSC' :
                        case 'BCC' : {
                            if (count($threeHighestDomestic) < 3) {
                                array_push($threeHighestDomestic, ['points'=>$cadetPoints['points'], 'index'=>$i]);
                            } else {
                                if ($cadetPoints['points'] > min($threeHighestDomestic)['points']) {
                                    $lowValuePosition = array_search(min($threeHighestDomestic)['points'], array_column($threeHighestDomestic, 'points'));
                                    $threeHighestDomestic[$lowValuePosition] = ['points'=>$cadetPoints['points'], 'index'=>$i];
                                }
                            }
                            if ($seriesData[$i]['eventType'] == 'BCC') $row['cadetNationals'] = $row['e'.$i.'_place'];
                            array_push($Domestic, (100 / $seriesData[$i]['entries']) * $row['e'.$i.'_place']);
                            break;
                        }
                        case 'EFC' : {
                            if (count($threeHighestInternational) < 3) {
                                array_push($threeHighestInternational, ['points'=>$cadetPoints['points'], 'index'=>$i]);
                            } else {
                                if ($cadetPoints['points'] > min($threeHighestInternational)['points']) {
                                    $lowValuePosition = array_search(min($threeHighestInternational)['points'], array_column($threeHighestInternational, 'points'));
                                    $threeHighestInternational[$lowValuePosition] = ['points'=>$cadetPoints['points'], 'index'=>$i];
                                }
                            }
                            array_push($International, (100 / $seriesData[$i]['entries']) * $row['e'.$i.'_place']);
                            break;
                        }
                    }       
                }
            }
            $row['pts'] = array_sum(array_column($threeHighestDomestic, 'points'));
            $row['pts'] += array_sum(array_column($threeHighestInternational, 'points'));

            $row['domestic'] = '-';
            if (count($Domestic) > 0) {
                $row['domestic'] = round(array_sum($Domestic) / count($Domestic), 2);        
            }
            
            $row['international'] = '-';
            if (count($International) > 0) {
                $row['international'] = round(array_sum($International) / count($International), 2);
            }
            
            for ($i=0;$i<count($threeHighestDomestic);$i++) {
                $row['e'.$threeHighestDomestic[$i]['index'].'_pointsHi'] = true;
            }

            $highestInternational = 0;
            for ($i=0;$i<count($threeHighestInternational);$i++) {
                if ($threeHighestInternational[$i]['points'] > $highestInternational ) {
                    $highestInternational = $threeHighestInternational[$i]['points'];
                }
                $row['e'.$threeHighestInternational[$i]['index'].'_pointsHi'] = true;
            }

            // Data for secondary sorting for fencers with equal points
            $row['highestInternational'] = $highestInternational;

            $row['blank'] = '';
            $res['data'][] = $row;
        }

        // As we have no placings yet, we need to sort the data and assign them here before we
        // send it back.

        uasort($res['data'], array($this, "ptsSort"));

        $res = array_values($res['data']);
        $points = null;
        $position = null;        
        $runningPosition = 0;        
        foreach ($res as &$row) {
            $runningPosition++;
            if ($row['pts'] <> $points) { 
                $points = $row['pts'];
                $position = $runningPosition;
            } 
            $row['position'] = $position;
        }
        $res['data'] = $res;
        return $res; 
    }    

    public function efcHistory($data) {
        
        $res['data'] = [];

        $seasonEnd = self::EFC_DATA_START;
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
                                   WHERE eventData.catID = ? AND results.eventCat = ? AND fencerClubID = ? AND eventType = \'EFC\' AND eventPosition <> 9999
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

    public function efcEventHistory($data) {
        
        $res['data'] = [];

        $params = null;

        if ($data['season'] > 0) {
            $this->setSeasonStartEnd($data['season']);
            $params[] = $this->seasonStart;
            $params[] = $this->seasonEnd;    
        } else {
            $params[] = self::EFC_DATA_START;
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
                                  WHERE (fullDate > ? and fullDate <= ?) AND eventData.catID = ? AND results.eventCat = ? AND eventType = \'EFC\' AND fencerClubID = ?
                                  GROUP BY events.eventName');
                
        $sql->execute($params);
        $eventData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $totalEntries = 0;

        foreach ($eventData as $row) {                        
            $totalEntries += $row['entries'];
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        $sql = $this->db->prepare('SELECT CAST(AVG(eventPosition) AS Integer) AS averagePosition
                                   FROM results 
                                   LEFT OUTER JOIN events ON events.ID  = results.eventID 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID 
                                   LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   WHERE (fullDate > ? and fullDate <= ?) AND eventData.catID = ? AND results.eventCat = ? AND eventType = \'EFC\' AND fencerClubID = ?');                
        $sql->execute($params);
        $ap = $sql->fetch(PDO::FETCH_ASSOC);      

        $res['ap'] =  $ap['averagePosition'];
        $res['te'] =  $totalEntries;

        return $res; 

    }
    
    public function getCadetPoints($data) {
        $sql = $this->db->prepare('SELECT eventPosition
                                   FROM results 
                                   WHERE eventID = :eventID AND fencerID = :fencerID AND dateID = :dateID');
        $sql->bindValue(":eventID", (int)$data['eventID']);
        $sql->bindValue(":dateID", (int)$data['dateID']);
        $sql->bindValue(":fencerID", (int)$data['fencerID']);
        $sql->execute();

        $place = $sql->fetch(PDO::FETCH_COLUMN);

        $points = $this->getUKMultipler($place, $data['entries'], $data['eventType'], false, $data['coef'], $data['eventID'], $data['f128']);

        return ['points' => $points, 'place' => $place];

    }

    public function getSeriesBreakdown($data) {
        $sql = $this->db->prepare('SELECT DISTINCT eventData.eventID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, eventDates.ID, entries, eventType, coef, f128
                                   FROM events 
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   WHERE '.$this->cadetSQL.' AND eventData.catID = :catID AND (fullDate >= :seasonStart and fullDate <= :seasonEnd) 
                                   ORDER BY fullDate ASC');
        $sql->bindValue(":catID", $data['catID']);
        $sql->bindValue(":seasonStart", $this->seasonStart);
        $sql->bindValue(":seasonEnd", $this->seasonEnd);
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUKMultipler($place, $entries, $eventType, $rep = false, $coef = 1, $eventID = null, $f128 = 0) {

        // Base points
        $points = 0;

        switch ($eventType) {
            case 'BRC' :
            case 'BSC' :
            case 'BCC' : {
                if (($place <= floor(($entries/ 100) * 80)) && ($place <= 64)) {
                    if ($place == 1) $points = 40;
                        else if ($place == 2) $points = 36; 
                        else if ($place == 3) $points = 32; 
                        else if ($place == 5) $points = 27; 
                        else if ($place == 6) $points = 26; 
                        else if ($place == 7) $points = 25; 
                        else if ($place == 8) $points = 24; 
                        else if ($place >= 9 && $place <= 16) $points = 16; 
                        else if ($place >= 17 && $place <= 32) $points = 8; 
                        else if ($place >= 33 && $place <= 64) $points = 4; 
                    if ($rep === 1) {
                        if ($place >= 9 && $place <= 12) $points = 20; 
                            else if ($place >= 17 && $place <= 24) $points = 12; 
                    }
                    if ($eventType == 'BCC') $points = $points * 1.2; // Nationals adjustment
                }    
                break;
            }
            case 'EFC' : {
                if ($place <= floor(($entries/ 100) * 80)) {
                    if ($place == 1) $points = 40 * $coef;
                        else if ($place == 2) $points = 36 * $coef; 
                        else if ($place == 3) $points = 32 * $coef; 
                        else if ($place == 5) $points = 27 * $coef; 
                        else if ($place == 6) $points = 26 * $coef; 
                        else if ($place == 7) $points = 25 * $coef; 
                        else if ($place == 8) $points = 24 * $coef; 
                        else if ($place >= 9 && $place <= 16) $points = 16 * $coef; 
                        else if ($place >= 17 && $place <= 32) $points = 8 * $coef; 
                        else if ($place >= 33 && $place <= 64) $points = 4 * $coef; 
                        else if ($place >= 65 && $place <= 128 && $f128 == 1) $points = 2 * $coef; 
                }
                if ((($eventID == self::EUROS) || ($eventID == self::WORLDS)) && ($place > 32)) {
                    // Only points for a L32 at major championships
                    $points = 0;
                }
                break;
            }
        }
        return $points;
    }

    public function efcCountryList($data) {
        $sql = $this->db->prepare('SELECT DISTINCT clubs.ID, clubName
                                   FROM results 
                                   INNER JOIN events ON events.ID = results.eventID     
                                   INNER JOIN clubs ON clubs.ID = results.fencerClubID
                                   WHERE results.eventCat IN (6,2) AND eventType = \'EFC\' AND clubs.ID <> 415
                                   ORDER BY clubName ASC');       
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    private function ptsSort($a, $b) {
        $rdiff = ($b['pts'] * 10) - ($a['pts'] * 10);
        if ($rdiff) return $rdiff; 
        $rdiff = $b['highestInternational'] - $a['highestInternational']; 
        if ($rdiff) return $rdiff; 
        return $a['cadetNationals'] - $b['cadetNationals'];
    }
        
    private function placeSuffix($number) {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
            else return $number. $ends[$number % 10];
    }

}