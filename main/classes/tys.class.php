<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class tys extends DB {
    
    private $seasonStart = null;
    private $seasonEnd = null;

    public function getSeriesSize($data) {
        $sql = $this->db->prepare('SELECT count(DISTINCT eventData.eventID)
                                   FROM events 
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   WHERE eventSeries = :series AND eventData.catID = :catID AND (fullDate >= :seasonStart and fullDate <= :seasonEnd)');
        $sql->bindValue(":series", $data['series']);
        $sql->bindValue(":catID", $data['catID']);
        $sql->bindValue(":seasonStart", $data['seasonStart']);
        $sql->bindValue(":seasonEnd", $data['seasonEnd']);
        $sql->execute();

        $this->setSeasonStartEnd($data['seasonStart'], $data['seasonEnd']);

        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    public function getSeriesBreakdown($data) {
        $sql = $this->db->prepare('SELECT DISTINCT eventData.eventID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, eventDates.ID
                                   FROM events 
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   WHERE eventSeries = :series AND eventData.catID = :catID AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd) 
                                   ORDER BY fullDate ASC');
        $sql->bindValue(":series", $data['series']);
        $sql->bindValue(":catID", $data['catID']);
        $sql->bindValue(":seasonStart", $this->getSeasonStart());
        $sql->bindValue(":seasonEnd", $this->getSeasonEnd());
        $sql->execute();

        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSeriesCompetitions($data) {
        $res['data'] = [];
        $sql = $this->db->prepare('SELECT events.ID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, entries, fencerFirstname, fencerSurname, results.fencerID, eventDates.ID AS dateID
                                   FROM events
                                   LEFT JOIN eventDates ON eventDates.eventID = events.ID
                                   LEFT JOIN eventData ON eventData.eventID = events.ID AND eventData.dateID = eventDates.ID
                                   LEFT JOIN results ON results.eventID = events.ID AND results.dateID = eventData.dateID and results.eventCat = eventData.catID
                                   LEFT JOIN fencers ON fencers.ID = results.fencerID 
                                   WHERE eventSeries = :series AND eventData.catID = :catID AND 
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd)  AND eventPosition = 1
                                   ORDER BY fullDate ASC');
        $sql->bindValue(":series", $data['series']);
        $sql->bindValue(":catID", $data['catID']);
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
        $sql = $this->db->prepare('SELECT results.fencerID, fencerFirstname, fencerSurname, yob, clubName, fencers.country, flagName
                                   FROM results 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND eventData.dateID = results.dateID
                                   LEFT OUTER JOIN eventDates ON eventDates.eventID = eventData.eventID AND eventDates.ID = results.dateID
                                   LEFT OUTER JOIN fencers ON fencers.ID = results.fencerID 
                                   LEFT OUTER JOIN events ON events.ID = results.eventID       
                                   LEFT JOIN clubs ON clubs.ID = (SELECT fencerClubID FROM results AS r 
                                                                  LEFT OUTER JOIN events AS e ON e.ID = r.eventID 
                                                                  WHERE fencers.ID = r.fencerID AND e.eventSeries = :series 
                                                                  ORDER BY r.ID DESC LIMIT 0, 1)
                                   LEFT OUTER JOIN flags ON flags.clubID = clubs.ID
                                   WHERE eventSeries = :series2 AND eventData.catID = :catID AND results.eventCat = :catID2 AND
                                         (fullDate >= :seasonStart and fullDate <= :seasonEnd) 
                                   GROUP BY results.fencerID');
        $sql->bindValue(":series", $data['series']);
        $sql->bindValue(":series2", $data['series']);
        $sql->bindValue(":catID", $data['catID']);
        $sql->bindValue(":catID2", $data['catID']);
        $sql->bindValue(":seasonStart", $data['seasonStart']);
        $sql->bindValue(":seasonEnd", $data['seasonEnd']);
        $sql->execute();

        $fencerData = $sql->fetchAll(PDO::FETCH_ASSOC);      

        $this->setSeasonStartEnd($data['seasonStart'], $data['seasonEnd']);

        $points = null;
        $position = null;        
        $runningPosition = 0;        

        $seriesData = $this->getSeriesBreakdown($data);        

        foreach ($fencerData as $row) {
           $fourHighest = [];
           for ($i=0; $i<count($seriesData); $i++) { 
                $tysData = $this->getEventPoints($seriesData[$i], $row['fencerID'], $data['catID']);
                $row['e'.$i.'_points'] = $tysData['points'];
                $row['e'.$i.'_place'] = $tysData['position'];
                $row['e'.$i.'_placeSuffix'] = $this->placeSuffix($tysData['position']);
                $row['e'.$i.'_eventName'] = $seriesData[$i]['eventName'];
                $row['e'.$i.'_eventDate'] = $seriesData[$i]['fullDateReal'];
                if ($tysData['points'] !== null) {
                    if (count($fourHighest) < 4) {
                        // counting score
                        array_push($fourHighest, ['points'=>$tysData['points'], 'index'=>$i]);
                    } else {
                        // four scores already counting need to compare
                        if ($tysData['points'] > min($fourHighest)['points']) {
                            $lowValuePosition = array_search(min($fourHighest)['points'], array_column($fourHighest, 'points'));
                            $fourHighest[$lowValuePosition] = ['points'=>$tysData['points'], 'index'=>$i];
                        }
                    }
                }
            }
            $row['pts'] = array_sum(array_column($fourHighest, 'points'));
            for ($i=0;$i<count($fourHighest);$i++) {
                $row['e'.$fourHighest[$i]['index'].'_pointsHi'] = true;
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


    private function ptsSort($a, $b) {
        return $b['pts'] - $a['pts'];
    }

    private function getEventPoints($data, $fencer, $cat) {

        $sql = $this->db->prepare('SELECT results.eventPosition AS position 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   WHERE results.eventID = :eventID AND eventDates.ID = :ID AND results.fencerID = :fencerID AND results.eventCat = :cat');
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->bindValue(":ID", $data['ID']);
        $sql->bindValue(":cat", $cat);
        $sql->bindValue(":fencerID", $fencer);
        $sql->execute();

        $row = $sql->fetch(PDO::FETCH_ASSOC);
        
        if ($row !== false) {
            if ($row['position'] == 1) { $points = 32; }
                else if ($row['position'] == 2) { $points = 26; }
                else if ($row['position'] == 3) { $points = 20; }
                else if (($row['position'] >= 5) &&  ($row['position'] <= 8)) { $points = 14; }
                else if (($row['position'] >= 9) &&  ($row['position'] <= 16)) { $points = 8; }
                else $points = 4;

            $row['points'] =  $points;
        }
//print_r($row);

        return $row;
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

    private function placeSuffix($number)
    {
        $ends = array('th','st','nd','rd','th','th','th','th','th','th');
        if ((($number % 100) >= 11) && (($number%100) <= 13)) return $number. 'th';
            else return $number. $ends[$number % 10];
    }
}
