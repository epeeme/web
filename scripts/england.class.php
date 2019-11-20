<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class england extends DB {
    
    private $seasonStart = null;
    private $seasonEnd = null;

    private function getExcludeString($data) {
        $exclude = "";
        if ($data['catID'] == 17 || $data['catID'] == 18) {
            // Exclude Elite Epee U17 from U14 list
            $sql = $this->db->prepare('SELECT eventData.ID FROM events
                                       LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                       LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID                                   
                                       WHERE (fullDate >= :seasonStart and fullDate <= :seasonEnd) AND eventSeries = \'EEJS\' AND (catID = 6 OR catID = 2)');        
            $sql->bindValue(":seasonStart", $data['seasonStart']);
            $sql->bindValue(":seasonEnd", $data['seasonEnd']);
            $sql->execute();

            $res = $sql->fetchALL(PDO::FETCH_COLUMN);

            if (count($res)) {
                $exclude = " AND eventData.ID NOT IN (".implode(',',$res).")";
            }
        }
        return $exclude;
    }

    public function getSeriesSize($data) {
        $sql = $this->db->prepare('SELECT count(DISTINCT eventData.ID)
                                   FROM events
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID                                   
                                   WHERE NIFF = 1 AND eventData.catID IN ('.implode(",", $this->getCatIDs($data['catID'])).')
                                         AND (fullDate >= :seasonStart and fullDate <= :seasonEnd)'.$this->getExcludeString($data));
        $sql->bindValue(":seasonStart", $data['seasonStart']);
        $sql->bindValue(":seasonEnd", $data['seasonEnd']);
        $sql->execute();

        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    public function getSeriesBreakdown($data) {
        $sql = $this->db->prepare('SELECT DISTINCT eventData.eventID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, eventDates.ID, eventData.catID, eventDates.year
                                   FROM events 
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   WHERE NIFF = 1 AND eventData.catID IN ('.implode(",", $this->getCatIDs($data['catID'])).') AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd)'.$this->getExcludeString($data).'
                                   ORDER BY fullDate ASC, eventName ASC, eventData.catID ASC');
        $sql->bindValue(":seasonStart", $this->getSeasonStart());
        $sql->bindValue(":seasonEnd", $this->getSeasonEnd());
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSeriesCompetitions($data) {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT events.ID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, entries, fencerFirstname, fencerSurname, eventData.catID, NIFFvalue, eventData.ID AS eventDataID, eventData.dateID, results.fencerID 
                                   FROM events
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   LEFT JOIN results ON results.eventID = events.ID AND results.dateID = eventData.dateID and results.eventCat = eventData.catID
                                   LEFT JOIN fencers ON fencers.ID = results.fencerID 
                                   WHERE NIFF = 1 AND eventData.catID IN ('.implode(",", $this->getCatIDs($data['catID'])).') AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd)  AND eventPosition = 1'.$this->getExcludeString($data).'
                                   ORDER BY fullDate ASC, eventName ASC, eventData.catID ASC');
        $sql->bindValue(":seasonStart", $data['seasonStart']);
        $sql->bindValue(":seasonEnd", $data['seasonEnd']);
        $sql->execute();

        $seriesData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $c = 1;
        foreach ($seriesData as $row) {
            $row['eventNum'] = $c++;
            $row['winner'] = $row['fencerFirstname']." ".$row['fencerSurname'];
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        return $res; 

    }

    public function getSeriesResults($data) {
        $res['data'] = [];
        $cIds = implode(",", $this->getCatIDs($data['catID']));
        $yobString = null; 
        switch ($data['catID']) {
            case 3: // u13
            case 4: { $yobString = "(yob >= ".($data['season'] - 12).' AND yob <= '.($data['season'] - 11).')';  break; }
            case 17: // u14
            case 18: { $yobString = "(yob >= ".($data['season'] - 13).' AND yob <= '.($data['season'] - 12).')';  break; }
            case 5: //u15
            case 1: { $yobString = "(yob >= ".($data['season'] - 14).' AND yob <= '.($data['season'] - 13).')';  break; }
            case 6: // u17
            case 2: { $yobString = "(yob >= ".($data['season'] - 15).' AND yob <= '.($data['season'] - 14).')'; break; }
        }

        $sql = $this->db->prepare('SELECT results.fencerID, fencerFirstname, fencerSurname, yob, clubName, fencers.country, flagName
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
                                   LEFT OUTER JOIN flags ON flags.clubID = clubs.ID
                                   WHERE NIFF = 1 AND eventData.catID IN ('.$cIds.') AND 
                                         results.eventCat IN ('.$cIds.') AND '.$yobString.' AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd) 
                                   GROUP BY results.fencerID');
        $sql->bindValue(":seasonStart", $data['seasonStart']);
        $sql->bindValue(":seasonEnd", $data['seasonEnd']);
        $sql->execute();

        $fencerData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $this->setSeasonStartEnd($data['seasonStart'], $data['seasonEnd']);

        $points = null;
        $position = null;        
        $runningPosition = 0;        

        $seriesData = $this->getSeriesBreakdown($data);        
        $goldenTickets = (int)$data['season'] <= 2018 ? $this->goldenTickets($data) : [];

        foreach ($fencerData as $row) {
           $fiveHighest = [];
           $row['goldenTicket'] = in_array($row['fencerID'], $goldenTickets);
           for ($i=0; $i<count($seriesData); $i++) { 
                $englandData = $this->getEventPoints($seriesData[$i], $row['fencerID']);
                $row['e'.$i.'_points'] = $englandData['points'];
                $row['e'.$i.'_place'] = $englandData['position'];
                $row['e'.$i.'_placeSuffix'] = $this->placeSuffix($englandData['position']);
                $row['e'.$i.'_eventName'] = $seriesData[$i]['eventName'];
                $row['e'.$i.'_eventDate'] = $seriesData[$i]['fullDateReal'];
                if ($englandData['points'] !== null) {
                    if (count($fiveHighest) < 5) {
                        // counting score
                        array_push($fiveHighest, ['points'=>$englandData['points'], 'index'=>$i,
                                                  'eventName' => $seriesData[$i]['eventName'],
                                                  'eventDate' => $seriesData[$i]['fullDateReal'],
                                                  'placeSuffix' => $this->placeSuffix($englandData['position'])]);
                    } else {
                        // five scores already counting need to compare
                        if ($englandData['points'] > min($fiveHighest)['points']) {
                            $lowValuePosition = array_search(min($fiveHighest)['points'], array_column($fiveHighest, 'points'));
                            $fiveHighest[$lowValuePosition] = ['points' => $englandData['points'], 
                                                               'index' => $i,
                                                               'eventName' => $seriesData[$i]['eventName'],
                                                               'eventDate' => $seriesData[$i]['fullDateReal'],
                                                               'placeSuffix' => $this->placeSuffix($englandData['position'])
                                                              ];
                        }
                    }
                }
            }
            $row['pts'] = array_sum(array_column($fiveHighest, 'points'));
            rsort($fiveHighest);

            for ($i=0;$i<5;$i++) {
                $row['e'.$i.'_pointsHi'] = isset($fiveHighest[$i]) !== false ? 
                                           (int)$fiveHighest[$i]['points'] >= 0 ? (int)$fiveHighest[$i]['points'] : '-'
                                           : '-';
                $row['e'.$i.'_pointseventName'] = isset($fiveHighest[$i]) !== false ? $fiveHighest[$i]['eventName'] : '-';
                $row['e'.$i.'_pointseventDate'] = isset($fiveHighest[$i]) !== false ? $fiveHighest[$i]['eventDate'] : '-';
                $row['e'.$i.'_pointsplaceSuffix'] = isset($fiveHighest[$i]) !== false ? $fiveHighest[$i]['placeSuffix'] : '-';
            }
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        // As we have no placings yet, we need to sort the data and assign them here before we
        // send it back.

        uasort($res['data'], array($this, "ptsSort"));
        $res = array_values($res['data']);
        $points = null;
        $position = null;        
        $positionReal = null;        
        $runningPosition = 0;        
        $runningPositionReal = 0;
        $trigger = 0;
        foreach ($res as &$row) {
            $runningPosition++;
            if ($row['country'] == 'ENG') ++$runningPositionReal;
            if (($row['pts'] <> $points) || (($trigger == 1) && ($row['country'] == 'ENG'))) { 
                $points = $row['pts'];
                $position = $runningPosition;
                $positionReal = $runningPositionReal;
                if ($row['country'] != 'ENG') {
                    $trigger = 1; 
                } else { 
                    $trigger = 0;
                }
            }  
            $row['position'] = $position;
            $row['positionDisplay'] = $row['country'] == 'ENG' ? $positionReal : '';
        }
        $res['data'] = $res;
        return $res; 
    }    

    private function ptsSort($a, $b) {
        return $b['pts'] - $a['pts'];
    }

    private function getEventPoints($data, $fencer) {
        $entries = (int)$data['year'] >= 2019 ? ' * entries' : '';
        $sql = $this->db->prepare('SELECT CASE 
                                      WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 0 THEN FLOOR(multiplier * NIFFValue'.$entries.') 
                                      WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 1 THEN FLOOR(multiplier_rep * NIFFValue'.$entries.') 
                                      ELSE 0 END AS points, results.eventPosition AS position 
                                   FROM results 
                                    INNER JOIN multipliers ON multipliers.position = results.eventPosition 
                                    INNER JOIN eventData ON eventData.eventID = results.eventID AND eventData.dateID = results.dateID AND eventData.catID = results.eventCat
                                    INNER JOIN eventDates ON eventDates.ID = eventData.dateID  AND eventDates.eventID = eventData.eventID
                                   WHERE results.eventID = :eventID AND results.dateID = :ID AND results.eventCat = :cat AND results.fencerID = :fencerID');
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->bindValue(":ID", $data['ID']);
        $sql->bindValue(":cat", $data['catID']);
        $sql->bindValue(":fencerID", $fencer);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);      
    }
    
    private function goldenTickets($data) {
        $sql = $this->db->prepare('SELECT fencerID 
                                   FROM results 
                                   INNER JOIN eventDates ON dateID = eventDates.ID 
                                   WHERE eventPosition <= 3 AND results.eventID = 30 
                                    AND (eventCat = 3 OR eventCat = 4 OR eventCat = 5 OR eventCat = 1) 
                                    AND year = :year');
        $sql->bindValue(":year", (int)$data['season']);
        $sql->execute();
        return $sql->fetchALL(PDO::FETCH_COLUMN);
    }

    private function setSeasonStartEnd($ss, $se) {
        $this->seasonStart = $ss;
        $this->seasonEnd = $se;
    }

    private function getSeasonStart() {
        return $this->seasonStart;
    }

    private function getSeasonEnd() {
        return $this->seasonEnd;
    }

    private function getCatIDs($catID) {
        $cats = null;
        switch ($catID) {
            case 6: {  $cats = [6,13,5,17]; break; }
            case 2: {  $cats = [2,14,1,18]; break; } 
            case 17: { $cats = [17,3,15,6]; break; } 
            case 18: { $cats = [18,4,16,2]; break; } 
            case 3: { $cats = [3,15]; break; } 
            case 4: { $cats = [4,16]; break; } 
            case 5: { $cats = [5,17,3,6]; break; } 
            case 1: { $cats = [1,18,4,2]; break; } 
        }
        return $cats;
    }

    private function placeSuffix($number)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
            else return $number. $ends[$number % 10];
    }
}
