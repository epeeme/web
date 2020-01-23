<?php

    ini_set('default_socket_timeout', 60);
    ini_set('max_execution_time', 0);

    $sourceData = "fieURLs.json";
    $urlsData = json_decode(file_get_contents($sourceData));

    foreach($urlsData AS $url) {
        $isAvailable = getWaybackRessponse($url[0]);
        if ($isAvailable !== false) {
            getWaybackHTMLResponse($isAvailable);
        }
    }

    function getWaybackRessponse($data) {
        $response = json_decode(file_get_contents('http://archive.org/wayback/available?url='.$data));
        if (!isset($response->archived_snapshots->closest->available)) {
            echo '                              '.$data.'\n';
        } else {
            if ($response->archived_snapshots->closest->available === true) {
                return $response->archived_snapshots->closest->url;
            } else {
                echo '                              '.$data.'\n';
            }
        }
    }

    function getWaybackHTMLResponse($url) {
        $response = file_get_contents($url);
        $rawData = strip_tags(get_istring_between($response, '<table id="Table4" cellspacing="4" cellpadding="0" width="100%" border="0">', '</table>'));
        $eventData = explode("\n", trim(preg_replace('/\t+/', '', $rawData)));
        $weapon = $eventData[26];
        echo $eventData[18]. "  ".$eventData[20]. "  ".$eventData[22]. "  ".$eventData[24]. "  ".$eventData[26]. "  ".$eventData[28]. "  ".$eventData[30]. "  ".$eventData[32]."    ".$url."\n";
    }

    function get_istring_between($string, $start, $end) {
        $ini = (stripos($string, $start) + strlen($start));
        return $ini !== stripos($string, $start) ? substr($string, $ini, (stripos($string, $end, $ini) - $ini)) : '';
    }

?>