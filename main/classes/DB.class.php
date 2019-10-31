<?php

class DB {

    protected $db = null;
    
    const SENIOR = "25,26,27";
 
    const YOUTH_NIF_CATS = "13,14,17,18,15,16,4,3,1,5";
    const BYC_ID = 29;
    const EYC_ID = 30;

    const UNATTACHED = 261;

    public function __construct($cfg) {
        try {
            $this->db = new PDO("mysql:host=".$cfg['db']['host'].";dbname=".$cfg['db']['database'].";charset=utf8mb4", 
                                              $cfg['db']['user'], $cfg['db']['password']);
        } catch (PDOException $exception) {
            throw new Exception ($exception->getMessage());
        }
    }

    private function getCadetYearOfBirth() {
        return date('n') < 5 ? date('Y') - 17 : date('Y') - 16;
    }

    public function hasFencedSenior($fencerID) {
        $sql = $this->db->prepare('SELECT ID FROM results WHERE fencerID = :id AND eventCat IN ('.self::SENIOR.') LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN) !== false ? true : false;
    }

    public function hasBeenRankedInTopX($fencerID, $x) {
        $sql = $this->db->prepare('SELECT ID FROM rollingRankingsTemp WHERE fencerID = :id AND rank <= :x LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->bindValue(":x", $x);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN) !== false ? true : false;
    }

    public function getYearOfBirth($fencerID) {
        $sql = $this->db->prepare('SELECT yob FROM fencers WHERE ID = :id');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    public function getSex($fencerID) {
        $sql = $this->db->prepare('SELECT sex FROM fencers WHERE ID = :id');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN);
    }

    public function getFencerRecordFromID($fencerID) {
        $sql = $this->db->prepare('SELECT BFA, fencerFirstname, fencerSurname, fencerFullname, yob, country, vanityName, sex, efr, fwID 
                                   FROM fencers WHERE ID = :id');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getFencerRecordFromVanity($vanityName) {
        $sql = $this->db->prepare('SELECT BFA, fencerFirstname, fencerSurname, fencerFullname, yob, country, sex, efr, fwID 
                                   FROM fencers WHERE vanityName = :vname');
        $sql->bindValue(":vname", $vanityName);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getFencerMedalSummary($fencerID) {
        $sql = $this->db->prepare('SELECT eventPosition, count(*) AS MedalCount
                                   FROM results 
                                   WHERE fencerID = :id AND eventPosition <= 3 
                                   GROUP BY eventPosition 
                                   ORDER BY eventPosition ASC');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFencerCurrentClub($fencerID) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName AS Club, clubs.ID AS ClubID, fullDate AS ClubLastRep
                                   FROM results 
                                   LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                   LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                   WHERE fencerID = :id AND region = 0 AND cty = 0 AND uni = 0 AND sch = 0 AND clubs.ID <> '.self::UNATTACHED.'
                                   ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : ['Club' => NULL, 'ClubID' => NULL, 'ClubLastRep' => NULL];
    }

    public function getFencerCurrentRegion($fencerID) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName AS Region, clubs.ID AS RegionID, fullDate AS RegionLastRep
                                   FROM results 
                                   LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                   LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                   WHERE fencerID = :id AND region = 1
                                   ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : ['Region' => NULL, 'RegionID' => NULL, 'RegionLastRep' => NULL];
    }

    public function getFencerCurrentSchool($fencerID) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName AS School, clubs.ID AS SchoolID, fullDate AS SchoolLastRep
                                   FROM results 
                                   LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                   LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                   WHERE fencerID = :id AND region = 0 AND sch = 1
                                   ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : ['School' => NULL, 'SchoolID' => NULL, 'SchoolLastRep' => NULL];
    }

    public function getFencerCurrentUni($fencerID) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName AS Uni, clubs.ID AS UniID, fullDate AS UniLastRep
                                   FROM results 
                                   LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                   LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                   WHERE fencerID = :id AND region = 0 AND uni = 1
                                   ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : ['Uni' => NULL, 'UniID' => NULL, 'UniLastRep' => NULL];
    }

    public function getFencerCurrentCountry($fencerID) {
        $sql = $this->db->prepare('SELECT DISTINCT clubName AS Country, clubs.ID AS CountryID, fullDate AS CountryLastRep
                                   FROM results 
                                   LEFT OUTER JOIN clubs ON results.fencerClubID = clubs.ID 
                                   LEFT OUTER JOIN eventDates ON results.dateID = eventDates.ID 
                                   WHERE fencerID = :id AND region = 0 AND cty = 1
                                   ORDER BY eventDates.fullDate DESC LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        $res = $sql->fetch(PDO::FETCH_ASSOC);
        return $res !== false ? $res : ['Country' => NULL, 'CountryID' => NULL, 'CountryLastRep' => NULL];
    }

    public function getFencerAffiliations($fencerID) {
        $club = $this->getFencerCurrentClub($fencerID);
        $region = $this->getFencerCurrentRegion($fencerID);
        $school = $this->getFencerCurrentSchool($fencerID);
        $uni = $this->getFencerCurrentUni($fencerID);
        $country = $this->getFencerCurrentCountry($fencerID);
        return array_merge($club, $region, $school, $uni, $country);
    }

    public function getCadetRank($fencerID) {
        $sql = $this->db->prepare('SELECT place FROM cadets WHERE fencerID = :id');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN) !== false ? true : false;
    }
 
    public function getFinishingPositions($fencerID) {
        $sql = $this->db->prepare('SELECT SUM(CASE WHEN eventPosition = 1 THEN 1 ELSE 0 END) AS first, 
                                          SUM(CASE WHEN eventPosition = 2 THEN 1 ELSE 0 END) AS second, 
                                          SUM(CASE WHEN eventPosition = 3 THEN 1 ELSE 0 END) AS third, 
                                          SUM(CASE WHEN eventPosition > 3 AND eventPosition < 9 THEN 1 ELSE 0 END) AS last8, 
                                          SUM(CASE WHEN eventPosition > 8 AND eventPosition < 17 THEN 1 ELSE 0 END) AS last16, 
                                          SUM(CASE WHEN eventPosition > 16 AND eventPosition < 33 THEN 1 ELSE 0 END) AS last32, 
                                          SUM(CASE WHEN eventPosition > 32 AND eventPosition < 65 THEN 1 ELSE 0 END) AS last64, 
                                          SUM(CASE WHEN eventPosition > 64 AND eventPosition < 129 THEN 1 ELSE 0 END) AS last128,
                                          SUM(CASE WHEN eventPosition > 128 AND eventPosition < 257 THEN 1 ELSE 0 END) AS last256,
                                          SUM(CASE WHEN eventPosition > 256 AND eventPosition < 513 THEN 1 ELSE 0 END) AS last512
                                   FROM results 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID AND eventData.CatID = results.eventCat 
                                   WHERE fencerID = :id');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getNumberOfCompsPerYear($fencerID) {
        $sql = $this->db->prepare('SELECT year, count(*) AS CompCount
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   WHERE fencerID = :id 
                                   GROUP BY year
                                   ORDER BY year ASC');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFencerResults($fencerID) {
        $sql = $this->db->prepare('SELECT year, DATE_FORMAT(fullDate,\'%d-%m-%Y\') AS eventDate, eventType, eventName, age, eventPosition, entries, lpjsPoints, NIFF, NIFFvalue,
                                          CASE 
                                            WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 0 && age != \'Snr\' && outOfAge = 0 THEN FLOOR(multiplier * NIFFValue) 
                                            WHEN eventPosition <= (entries/2) && NIFF = 1 && repechage = 1 && age != \'Snr\' && outOfAge = 0 THEN FLOOR(multiplier_rep * NIFFValue) 
                                            WHEN age = \'Snr\' THEN \'-\'
                                            ELSE 0 
                                          END AS ORP,                                          
                                          repechage, categories.ID, eventDates.ID, results.eventID, sex, elitePoints, results.ID, NIF2, Points1, Points2, Rank1, Rank2, nominated, eventData.ID
                                   FROM results
                                   LEFT OUTER JOIN events ON events.ID = results.eventID
                                   LEFT OUTER JOIN categories ON categories.ID = results.eventCat    
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   LEFT OUTER JOIN eventData ON eventData.eventID = results.eventID AND results.dateID = eventData.dateID AND eventData.CatID = results.eventCat
                                   LEFT OUTER JOIN multipliers ON position = eventPosition
                                   WHERE fencerID = :id
                                   ORDER BY fullDate DESC, age');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eventHasPouleResults($fencerID, $eventID) {
        $sql = $this->db->prepare('SELECT ID FROM pouleResults 
                                   WHERE fencerID = :id AND eventDataID = :eid LIMIT 0,1');
        $sql->bindValue(":id", $fencerID);
        $sql->bindValue(":eid", $eventID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_COLUMN) !== false ? true : false;
    }

    public function getSeniorSeasonRankings($fencerID, $catID, $seasonStart, $seasonEnd) {
        $sql = $this->db->prepare('SELECT DISTINCT FROM_UNIXTIME(epoch), DATE_FORMAT(fullDate,\'%d-%m-%Y\'), epoch, eventName, eventPosition, NIF2, rank, Points2, entries, NIFFvalue, year, MONTHNAME(fullDate), eventData.eventID 
                                   FROM rollingRankingsTemp 
                                   LEFT OUTER JOIN events ON events.ID = rollingRankingsTemp.eventID
                                   LEFT OUTER JOIN eventData ON eventData.dateID = rollingRankingsTemp.dateID AND eventData.eventID = rollingRankingsTemp.eventID
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = rollingRankingsTemp.dateID 
                                   LEFT OUTER JOIN results ON results.dateID = rollingRankingsTemp.dateID AND results.eventID = rollingRankingsTemp.eventID AND results.fencerID = rollingRankingsTemp.fencerID
                                   WHERE epoch >= :seasonStart AND epoch <= :seasonEnd AND rollingRankingsTemp.fencerID = :id AND catID = :catid AND eventPosition IS NOT NULL
                                   ORDER BY epoch ASC');
        $sql->bindValue(":id", $fencerID);
        $sql->bindValue(":catid", $catID);
        $sql->bindValue(":seasonStart", $seasonStart);
        $sql->bindValue(":seasonEnd", $seasonEnd);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastBYCResultFencer($fencerID) {
        $sql = $this->db->prepare('SELECT eventCat, eventPosition, year 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   WHERE eventCat IN ('.self::YOUTH_NIF_CATS.') AND results.eventID = '.self::BYC_ID.' AND fencerID = :id
                                   ORDER BY year DESC LIMIT 0, 1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastEYCResultFencer($fencerID) {
        $sql = $this->db->prepare('SELECT eventCat, eventPosition, year 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   WHERE eventCat IN ('.self::YOUTH_NIF_CATS.') AND results.eventID = '.self::EYC_ID.' AND fencerID = :id
                                   ORDER BY year DESC LIMIT 0, 1');
        $sql->bindValue(":id", $fencerID);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function getLastBYCResultL16() {
        $sql = $this->db->prepare('SELECT eventCat, eventPosition, fencerFirstname, fencerSurname, results.fencerID 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   LEFT OUTER JOIN fencers ON fencers.ID = results.fencerID 
                                   WHERE eventCat IN ('.self::YOUTH_NIF_CATS.') AND eventPosition < 17 AND results.eventID = '.self::BYC_ID);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLastEYCResultL16() {
        $sql = $this->db->prepare('SELECT eventCat, eventPosition, fencerFirstname, fencerSurname, results.fencerID 
                                   FROM results 
                                   LEFT OUTER JOIN eventDates ON eventDates.ID = results.dateID 
                                   LEFT OUTER JOIN fencers ON fencers.ID = results.fencerID 
                                   WHERE eventCat IN ('.self::YOUTH_NIF_CATS.') AND eventPosition < 17 AND results.eventID = '.self::EYC_ID);
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTop20Cadets() {
        $sql = $this->db->prepare('SELECT place, fencerFirstname, fencerSurname, cadets.fencerID, cadets.sex 
                                   FROM cadets 
                                   LEFT OUTER JOIN fencers ON fencers.ID = cadets.fencerID 
                                   WHERE place <= 20 AND yob >= '.$this->getCadetYearOfBirth().'
                                   ORDER BY place ASC');
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSeniorRankMaxRange($fencerID, $start, $end) {
        $sql = $this->db->prepare('SELECT rank, epoch 
                                   FROM rollingRankingsTemp 
                                   WHERE  fencerID = :id AND epoch < :start AND epoch > :end
                                   ORDER BY epoch DESC LIMIT 0, 1');
        $sql->bindValue(":id", $fencerID);
        $sql->bindValue(":start", $start);
        $sql->bindValue(":end", $end);
        $sql->execute();
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

}
