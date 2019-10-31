<?php

class junior {

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

}

