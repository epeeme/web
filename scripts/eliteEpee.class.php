<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once "DB.class.php";

class eliteEpee extends DB {
    
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
        $sql = $this->db->prepare('SELECT events.ID, eventName, fullDate, DATE_FORMAT(fullDate,\'%D %b %Y\') AS fullDateReal, entries, fencerFirstname, fencerSurname, results.fencerID, eventData.dateID
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
        $sql = $this->db->prepare('SELECT results.fencerID, fencerFirstname, fencerSurname, SUM(elitePoints) AS pts, yob, clubName, fencers.country, flagName
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
                                   GROUP BY results.fencerID
                                   ORDER BY pts DESC');
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
            $runningPosition++;
            if ($row['pts'] <> $points) { 
                $points = $row['pts'];
                $position = $runningPosition;
            } 
            $row['position'] = $position;

            for ($i=0; $i<count($seriesData); $i++) { 
                $eliteData = $this->getEventPoints($seriesData[$i], $row['fencerID']);
                $row['e'.$i.'_points'] = $eliteData['points'];
                $row['e'.$i.'_place'] = $eliteData['position'];
                $row['e'.$i.'_placeSuffix'] = $this->placeSuffix($eliteData['position']);
                $row['e'.$i.'_eventName'] = $seriesData[$i]['eventName'];
                $row['e'.$i.'_eventDate'] = $seriesData[$i]['fullDateReal'];
            }
            $row['blank'] = '';
            $res['data'][] = $row;
        }

        return $res; 
    }    

    private function getEventPoints($data, $fencer) {

        $sql = $this->db->prepare('SELECT elitePoints AS points, results.eventPosition AS position 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   WHERE results.eventID = :eventID AND eventDates.ID = :ID AND results.fencerID = :fencerID');
        $sql->bindValue(":eventID", $data['eventID']);
        $sql->bindValue(":ID", $data['ID']);
        $sql->bindValue(":fencerID", $fencer);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);      
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
