<?php

/* Functions dealing with stinging, which is searching by color
 * 
 * 
 */
include('../algorithmns.php');

function hueCount() {
    // list of hexcodes to process by hue value
    // granularity no used 

    $colors = array();
    $trending = array();
    $items = array();
    $timeAgo = strtotime('-6 month', time());
    $itemQuery = "SELECT * FROM item WHERE 'time' > '" . $timeAgo . "' ORDER BY 'time'";
    $itemResult = mysql_query($itemQuery);
    while ($item = mysql_fetch_array($itemResult)) {

        $hex = $item['code'];
        $key = $hex[0] . $hex[2] . $hex[4];

        if (array_key_exists($key, $colors)) {
            $colors[$key]++;
        } else {
            $colors[$key] = 1;
        }
    }


    arsort($colors);
    
    $trending[] = current(array_keys($colors));
        
    
    foreach($colors as $key => $val){
        $hex = $key[0]."0".$key[1]."0".$key[2]."0";
        echo "<span style='background-color:#$key;'>#". $key . "=>" . $val . "<br/></span>";
        $trending[] = next(array_keys($colors));
        array_shift($colors);
        if($count>15){
            break;
        }
    }
    
}

?>
