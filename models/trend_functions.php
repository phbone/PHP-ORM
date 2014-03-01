<?php

/* Functions dealing with stinging, which is searching by color
 * 
 * 
 */
include('../algorithmns.php');



function hueCount($itemList) {
    // list of hexcodes to process by hue value
    // granularity no used 
        $colors = array();
        $length = count($itemList);
       
        
        for ($i = 0; $i < $length; $i++) {
                $hexcode = $itemList[$i];
                list($h, $s, $l) = hex_2_hsl($hexcode);
                echo round($h,1)."<br/>";
                if (array_key_exists($h, $colors)) {
                    $colors[$h]++;
                } else {
                    $colors[$h] = 1;
            }
        }
        arsort($colors);
        print_r($colors);
        return $colors;
    }

?>
