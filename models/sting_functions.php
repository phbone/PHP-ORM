<?php

/* Functions dealing with stinging, which is searching by color
 * 
 * 
 */

function stingColor($hexcode) {
    // find all clothing attached to the input color
    $h = str_split($hexcode);
    $r = intval($h[0]);
    $g = intval($h[2]);
    $b = intval($h[4]);
    $stingQry = "SELECT * FROM item WHERE code LIKE '%{$r}_{$g}_{$b}_%'";
    $stingRst = mysql_query($stingQry);
    while ($item = mysql_fetch_array($stingRst)) {
        $itemObject = new item_object;
        $itemObject = returnItem($item['itemid']);
        formatItem($userid, $itemObject);
    }
}

?>
