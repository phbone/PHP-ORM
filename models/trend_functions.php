<?php

/* Functions dealing with stinging, which is searching by color
 * 
 * 
 */
include('../algorithmns.php');

function trendingHex() {
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

    $count = 0;
    foreach ($colors as $key => $val) {
        $key = strval($key);
        $hex = $key[0] . "0" . $key[1] . "0" . $key[2] . "0";
        $text = fontColor($hex);
        echo "<span class='colorTags' onclick=\"viewItemsTaggedWith('$hex')\" style='background-color:#$hex;color:#$text'>#" . $hex . "</span><br/>";
        $trending[] = $hex;
        if ($count > 15) {
            break;
        }
        $count++;
    }
    return $trending;
}

function trendingItems($trendingHex) {

    for ($i = 0; $i < count($trendingHex); $i++) {
        // select 10 tags with the most 
        echo "<div class='taggedItems " . $trendingHex[$i] . "'>";
        stingColor($trendingHex[$i]);
        echo "</div>";
    }
}

?>
