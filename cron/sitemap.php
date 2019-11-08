<?php

require_once "../main/classes/Config.class.php";
require_once "../main/classes/DB.class.php";

class sitemap extends DB {

    private $xmlContent = null;

    public function createSitemap() {
        $this->xmlContent = $this->getXMLHeader();
        $this->createFencerLinks();
        $this->createEventLinks();
        $this->createResultLinks();        
        $this->xmlContent .= $this->getXMLFooter();
        return $this->xmlContent;
    }

    public function getXMLHeader() {

        $today = date("Y-m-d");
        return  <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>https://epee.me/</loc>
        <lastmod>{$today}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
    <url>
        <loc>https://epee.me/england.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>    
    <url>
        <loc>https://epee.me/elite.php</loc>
        <changefreq>monthly</changefreq>
        <priority>0.5</priority>
    </url>    
    <url>
        <loc>https://epee.me/lpjs.php</loc>
        <changefreq>weekly</changefreq>
        <priority>0.5</priority>
    </url>        

XML;
    }

    public function getXMLFooter() {
        return '</urlset>';
    }

    public function createFencerLinks() {        
        $fencerData = $this->getFencerIDs();
        foreach ($fencerData as $fencer) {
            $this->xmlContent .= <<<XML
    <url>
        <loc>https://epee.me/fencer.php?f={$fencer['fencerID']}</loc>
        <lastmod>{$fencer['lastUpdated']}</lastmod>
        <changefreq>monthly</changefreq>
        <priority>0.9</priority>
    </url>

XML;
        }
    }    

    public function createEventLinks() {        
        $eventData = $this->getEventIDs();
        foreach ($eventData as $event) {
            $this->xmlContent .= <<<XML
    <url>
        <loc>https://epee.me/event.php?e={$event['eventID']}&amp;d={$event['dateID']}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.8</priority>
    </url>

XML;
        }
    }

    public function createResultLinks() {        
        $resultData = $this->getResultIDs();
        foreach ($resultData as $result) {
            $this->xmlContent .= <<<XML
    <url>
        <loc>https://epee.me/result.php?r={$result['resultID']}</loc>
        <changefreq>yearly</changefreq>
        <priority>0.8</priority>
    </url>

XML;
        }
    }

    public function getFencerIDs() {
        $sql = $this->db->prepare('SELECT fencers.id AS fencerID, MAX(fullDate) AS lastUpdated
                                   FROM fencers
                                   INNER JOIN results ON results.fencerID = fencers.ID
                                   INNER JOIN eventDates ON eventDates.ID  = results.dateID
                                   GROUP BY fencerID');
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getEventIDs() {
        $sql = $this->db->prepare('SELECT events.id AS eventID, eventDates.ID AS dateID
                                   FROM events
                                   INNER JOIN eventDates ON eventDates.eventID  = events.ID');
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResultIDs() {
        $sql = $this->db->prepare('SELECT eventData.id AS resultID FROM eventData');
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

}

$gS = new Config();
$cfg = $gS->getSettings();

$sMap = new sitemap($cfg);

$siteMapXML = $sMap->createSitemap();

$fp = fopen('../sitemap.xml', 'wb');
fwrite($fp, $siteMapXML);
fclose($fp);
