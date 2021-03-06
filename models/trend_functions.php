<?php

/* Functions dealing with stinging, which is searching by color
 * 
 * 
 */
include('../algorithmns.php');

/** HELPER FUNCTIONS **/

//function convert_24bit_to_9bit($hex){
//    /** Return the 9 bit string equivalent of a given 24 bit color **/
//    
//    // get the exact decimal equivalent by multiplying each component by 7/255
//    $redValue = hexdec(substr($hex, 0, 2))*(7/255);
//    $greenValue = hexdec(substr($hex, 2, 2))*(7/255);
//    $blueValue = hexdec(substr($hex, 4, 2))*(7/255);
//
//    // round down the red value
//    $r = round($redValue, 0, PHP_ROUND_HALF_DOWN);
//    // to ensure the relative values are similar, round the difference between
//    // the components and add it to the rounded value
//    $g = $r + round(round(($greenValue - $redValue)*10)/10);
//    $b = $g + round(round(($blueValue - $greenValue)*10)/10);
//    // construct the final string
//    $color9bit = strval($r) . strval($g) . strval($b);
//    
//    return $color9bit;
//}
//
//function convert_9bit_to_24bit($color9bit){
//    /** Return the 24 bit representation of a given 9 bit string color **/
//    
//    // calculate the hex representation of 
//    $R = ($color9bit[0] == '0')? '00' : dechex(intval($color9bit[0])*(255/7));
//    $G = ($color9bit[1] == '0')? '00' : dechex(intval($color9bit[1])*(255/7));
//    $B = ($color9bit[2] == '0')? '00' : dechex(intval($color9bit[2])*(255/7));
//    $hex = $R . $G . $B;
//    
//    return $hex;
//}


function hundredthItemId(){
    /** Find the id of the 100th most recent item **/
    // query the 100 most recently uploaded items (with the largest item ids
    $query = "SELECT itemid FROM item ORDER BY itemid DESC LIMIT 100,1";
    $queryResult = mysql_query($query);
    $selectedRow = mysql_fetch_assoc($queryResult);
    $hundredthItemId = $selectedRow['itemid'];
    
    return $hundredthItemId;
}

function trendingHex() {
    /***
    Returns the 5 most trending 9-bit colors
    ***/

    $colors = array(); // key-value counting array
    $trending = array();
    // $timeAgo = strtotime('-4 month', time());
    
    // Find the 100 most recent items
    $itemQuery = "SELECT * FROM item ORDER BY itemid DESC LIMIT 0,100";
    $itemResult = mysql_query($itemQuery);
    while ($item = mysql_fetch_array($itemResult)) {
        $hex = $item['code'];
        // Convert the hex color into a 9 bit string
//        $color9bit = convert_24bit_to_9bit($hex);
        $color9bit = "111";
        
        if (array_key_exists($color9bit, $colors)) {
            $colors[$color9bit]++;
        } else {
            $colors[$color9bit] = 1;
        }
    }

    arsort($colors); // sort the colors in descending orer of occurences
    // retrieve the top 5 most occuring colors
    $trendingColors = array_keys(array_slice($colors, 0, 5, true));
    foreach ($trendingColors as $key) {
        // ensure that $key is a string (PHP casts integer like keys strings to integers)
        $key = strval($key);
        // Compute the corresponding hex value (if it's 0, hex value is '00') 
        $R = ($key[0] == '0')? '00' : dechex(intval($key[0])*(255/7));
        $G = ($key[1] == '0')? '00' : dechex(intval($key[1])*(255/7));
        $B = ($key[2] == '0')? '00' : dechex(intval($key[2])*(255/7));
        $hex = $R . $G . $B;
        // echo the tag html
        echo "<span class='colorTags' onclick=\"viewItemsTaggedWith('$key')\" style='background-color:#$hex;'> #" . $hex . "</span><br/>";
        }
    return $trendingColors;
}

function trendingTags() {
    /***
    Returns the 10 most trending tags within the 100 most recent items
    ***/

    $trendingTags = array();

    $hundredthItemId = hundredthItemId(); // find the id of the hundredth item
    
    // create a table containing all tagging events in the 100 most recent items
    $itemQuery = "SELECT * FROM tagmap LEFT JOIN item on item.itemid = tagmap.itemid WHERE 'item.itemid' >= '" . $hundredthItemId . "'";
    $itemResult = mysql_query($itemQuery);
    // create an array with all the tag ids
    while ($itemTagmap = mysql_fetch_array($itemResult)) {
        $trendingTags[] = $itemTagmap['tagid'];
    }

    $trendingTagSort = array_count_values($trendingTags); //Counts the values in the array, returns associatve array
    arsort($trendingTagSort); //Sort it from highest to lowest
    $trendingTagDict = array_keys($trendingTagSort); //Split the array so we can find the most occuring key
    //The most occuring value is $trendingTagKey[0][1] with $trendingTagKey[0][0] occurences.";

    $arrayLength = count($trendingTagDict);
    $tagCount = $arrayLength;
    if ($arrayLength > 10) {
        $tagCount = 10;
    }
    $trendingTags = array();

    for ($i = 0; $i < $tagCount; $i++) {

        if (count($trendingTagDict) == count(array_unique($trendingTagDict))) {
            $tag = database_fetch("tag", "tagid", $trendingTagDict[$i]);
        } else {
            $tag = database_fetch("tag", "tagid", $trendingTagDict[$i][1]);
        }
        echo "<span class='tagLinks' onclick=\"viewItemsTaggedWith('" . $tag['name'] . "')\">#" . $tag['name'] . "</span><br/>";
        $trendingTags[] = $tag['tagid'];
    }
    return $trendingTags;
}

/** END OF HELPER FUNCTIONS **/


function trendingItems($trendingTags, $trendingColor){
/***
This function returns the 30 most trending items ordered by the number of trending tags on the item
***/
    $allTrendingItemsCount = array(); // a temporary array to hold a count of all possible trending items
    $mostTrendingItems = array(); // The array to contain the final 30 most trending items
    
    $hundredthItemId = hundredthItemId(); // find the id of the hundredth item
    
    /** Find the the items which have both the trending color and one of the trending tags within the last 100 items 
        This is done by looping over the all colors and filtering each with the trending tags **/
    foreach($trendingColor as $color9bit){
        // Formulate all conditions
        /** Color conditions **/
        // ensure the color is a string
        $color9bit = strval($color9bit);
        // Slpit the 9-bit into the 3 components
        $r = $color9bit[0];
        $g = $color9bit[1];
        $b = $color9bit[2];

        // Create the color matching condition for every components
        $redCondition = "ROUND((CONVERT(CONV(SUBSTR(item.code, 1, 2), 16, 10), UNSIGNED))*(7/255)) = ".$r;
        $greenCondition = "ROUND((CONVERT(CONV(SUBSTR(item.code, 3, 2), 16, 10), UNSIGNED))*(7/255)) = ".$g;  
        $blueCondition = "ROUND((CONVERT(CONV(SUBSTR(item.code, 5, 2), 16, 10), UNSIGNED))*(7/255)) = ".$b;
        // The full color matching condition
        $colorCondition = $redCondition . " AND " . $greenCondition . " AND " . $blueCondition;
        
        /** Tags conditions **/
        $tagsCondition = "tagmap.tagid IN (".join(',', $trendingTags).")";
        
        // Formulate the final query condition
        $queryCondition = " AND ".$colorCondition." AND ".$tagsCondition;
        
        // Final query
        $query = "SELECT * FROM tagmap LEFT JOIN item on tagmap.itemid = item.itemid WHERE item.itemid >= ".$hundredthItemId.$queryCondition;
        
        $result = mysql_query($query);
        // check for errors
        if(!$result) die("Query failed : ".mysql_error()."\n\n failed Query: ".$query);
        
        // Retrieve all items ids and keep track of their occurences count
        while($item = mysql_fetch_array($result)){
            $itemid = $item['itemid'];
            // if itemid already exists increment count
            if(isset($allTrendingItemsCount[$itemid])){
                $allTrendingItemsCount[$itemid]++;
            }
            // otherwise add it to the keys
            else $allTrendingItemsCount[$itemid] = 1;
        }
    }
    arsort($allTrendingItemsCount); // sort the array in descending order of counting
    // retrieve the keys (item ids) of the first 30 items
    $mostTrendingItems = array_keys(array_slice($allTrendingItemsCount, 0, 30, true));
    
//    // if we have 30 items then we are done!
//    if(count($mostTrendingItems) == 30) return $mostTrendingItems;
//    
//    // otherwise, add elements with with the trending color but not the trending tags
//    /** tags condition **/
//    $noTagsCondition = "tagmap.tagid NOT IN (".join(',', $trendingTags).")";
//    
//    $queryCondition = " AND ".$colorCondition." AND ".$noTagsCondition;
//    $query = "SELECT * FROM tagmap LEFT JOIN item on tagmap.itemid = item.itemid WHERE item.itemid >= ".$hundredthItemId.$queryCondition;
//    
//    $result = mysql_query($query);
//    // check for errors
//    if(!$result) die("Query failed : ".mysql_error()."\n\n failed Query: ".$query);
//    
//    // append more items until you reach 30 or no more items are found
//    while($item = mysql_fetch_array($result) && count($mostTrendingItems) < 30){
//        $mostTrendingItems[] = $item['itemid'];
//    }
//    
//    // if we have 30 items then we are done!
//    if(count($mostTrendingItems) == 30) return $mostTrendingItems;
//    
//    // otherwise, add items containing just the tags and not the color
//    $queryCondition = " AND ".$tagsCondition;
//    $query = "SELECT * FROM tagmap LEFT JOIN item on tagmap.itemid = item.itemid WHERE item.itemid >= ".$hundredthItemId.$queryCondition;
//    
//    $result = mysql_query($query);
//    // check for errors
//    if(!$result) die("Query failed : ".mysql_error()."\n\n failed Query: ".$query);
//    
//    // append more items until you reach 30 or no more items are found
//    while($item = mysql_fetch_array($result) && count($mostTrendingItems) < 30){
//        $mostTrendingItems[] = $item['itemid'];
//    }
    
    return $mostTrendingItems;
}


/*** OLD FUNCTIONS ***/

//function trendingItemsColor($trendingHex) {
//
//    for ($i = 0; $i < count($trendingHex); $i++) {
//        // select 10 tags with the most 
//        echo "<div class='taggedItems " . $trendingHex[$i] . "'>";
//        stingColor($trendingHex[$i]);
//        echo "</div>";
//    }
//}
//
//function trendingItems($trendingTags, $friend_array) {
//    $existingItems = array();
//    for ($i = 0; $i < count($trendingTags); $i++) {
//        // select 10 tags with the most
//        $tagResult = database_query("tagmap", "tagid", $trendingTags[$i]);
//        while ($tagmap = mysql_fetch_array($tagResult)) {
//            $item = database_fetch("item", "itemid", $tagmap['itemid']);
//
//            // prevents an item appearing multiple times from having 2 trending tags
//            // prevents any items from friends
//            if (!in_array($tagmap['itemid'], $existingItems) && !in_array($item['userid'], $friend_array)) {
//                $item_object = returnItem($tagmap['itemid']);
//                $tags = str_replace("#", " ", $item_object->tags);
//                echo "<div class='taggedItems" . $tags . "'>";
//                formatItem($userid, $item_object);
//                echo "</div>";
//                $existingItems[] = $tagmap['itemid'];
//            }
//        }
//    }
//}

?>
