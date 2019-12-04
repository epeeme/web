<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class cadet extends DB {

    private $seasonStart = null;
    private $seasonEnd = null;

    // As Cadet Rankings are made up of BRC and EFC events, a pseudo series needs to be created
    private $cadetSQL = '(((eventType = \'BRC\' OR eventType = \'BCC\') AND nominated IN (1,2,3)) OR (eventType = \'EFC\' AND nominated IN (1,2,3)))';

    // Include only fencers from certain countries in the ranking list
    private $countries = array('\'ENG\'', '\'SCO\'', '\'WAL\'', '\'GBR\'', '\'NIR\'');
    
    private function setSeasonStartEnd($season) {

        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = 197 AND year = :year');
        $sql->bindValue(":year", $season);
        $sql->execute();
        $this->seasonStart = $sql->fetch(PDO::FETCH_COLUMN);
        $sql = $this->db->prepare('SELECT DISTINCT fullDate 
                                   FROM eventData LEFT JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventData.dateID = eventDates.id 
                                   WHERE eventData.eventID = 197 AND year = :year');
        $sql->bindValue(":year", ($season + 1));
        $sql->execute();
        $this->easonEnd = $sql->fetch(PDO::FETCH_COLUMN);

        if (empty($this->easonEnd)) {
            $this->seasonEnd = date('Y-m-d');
        }

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
        $sql = $this->db->prepare('SELECT results.fencerID, fencerFirstname, fencerSurname, yob, clubName, fencers.country, flagName, efr
                                   FROM results 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND eventData.dateID = results.dateID
                                   LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   LEFT OUTER JOIN fencers ON fencers.ID = results.fencerID 
                                   LEFT OUTER JOIN events ON events.ID = results.eventID       
                                   LEFT JOIN clubs ON clubs.ID = (SELECT fencerClubID FROM results AS r 
                                                                  LEFT OUTER JOIN events AS e ON e.ID = r.eventID 
                                                                  WHERE fencers.ID = r.fencerID AND '.$this->cadetSQL.'
                                                                  ORDER BY r.ID DESC LIMIT 0, 1)
                                   LEFT OUTER JOIN flags ON flags.clubID = clubs.ID
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
           $fourHighest = [];
           for ($i=0; $i<count($seriesData); $i++) { 

                $cadetPoints = $this->getCadetPoints(['fencerID' => $row['fencerID'], 
                                                      'dateID' => $seriesData[$i]['ID'], 
                                                      'eventID' => $seriesData[$i]['eventID'],
                                                      'eventType' => $seriesData[$i]['eventType'],
                                                      'entries' => $seriesData[$i]['entries']]);
                $row['e'.$i.'_points'] = $cadetPoints['points'];
                $row['e'.$i.'_place'] = (int)$cadetPoints['place'];
                $row['e'.$i.'_placeSuffix'] = $this->placeSuffix($cadetPoints['place']);
                $row['e'.$i.'_eventName'] = $seriesData[$i]['eventName'];
                $row['e'.$i.'_eventDate'] = $seriesData[$i]['fullDateReal'];
                /*if ($lpjsData['points'] !== null) {
                    if (count($fourHighest) < 4) {
                        // counting score
                        array_push($fourHighest, ['points'=>$lpjsData['points'], 'index'=>$i]);
                    } else {
                        // four scores already counting need to compare
                        if ($lpjsData['points'] > min($fourHighest)['points']) {
                            $lowValuePosition = array_search(min($fourHighest)['points'], array_column($fourHighest, 'points'));
                            $fourHighest[$lowValuePosition] = ['points'=>$lpjsData['points'], 'index'=>$i];
                        }
                    }
                }*/
            }
            /*$row['pts'] = array_sum(array_column($fourHighest, 'points'));
            for ($i=0;$i<count($fourHighest);$i++) {
                $row['e'.$fourHighest[$i]['index'].'_pointsHi'] = true;
            }*/
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        // As we have no placings yet, we need to sort the data and assign them here before we
        // send it back.

        /*uasort($res['data'], array($this, "ptsSort"));
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
        $res['data'] = $res;*/
        return $res; 
    }    

    public function getCadetPoints($data) {
        $sql = $this->db->prepare('SELECT eventPosition
                                   FROM results 
                                   WHERE eventID = :eventID AND fencerID = :fencerID AND dateID = :dateID');
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->bindValue(":dateID", $data['dateID']);
        $sql->bindValue(":fencerID", $data['fencerID']);
        $sql->execute();

        $place = $sql->fetch(PDO::FETCH_COLUMN);

        $points = $this->getUKMultipler($place, $data['entries'], $data['eventType']);

        return ['points' => $points, 'place' => $place];

    }

    public function getSeriesBreakdown($data) {
        $sql = $this->db->prepare('SELECT DISTINCT eventData.eventID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, eventDates.ID, entries, eventType
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

            if ($eventType == 'BCC') $points = $points * 1.2; // Nationals adjustment
            // if ($eventCat == 21 || $eventCat == 22) $points = $points * 1.5; // U20 adjustment (legacy >= 2019)

        }
        return $points;
    }

    private function placeSuffix($number)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
            else return $number. $ends[$number % 10];
    }

}

