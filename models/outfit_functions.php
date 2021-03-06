<?php

/* Functions dealing with the formatting of outfits, including outfit items,
 * uses functions dealing with the outfit class/global_objects
 * 
 */

function formatOutfit($userid, $outfitid) {
    // takes in the outfit id and returns outfit Object
    $outfitObject = returnOutfit($outfitid);
    if (!$outfitObject->name) {
        $outfitObject->name = "New Outfit";
    }
    echo "<div class='outfitContainer' id='outfit" . $outfitObject->outfitid . "' style='background-color:#" . $outfitObject->item1->hexcode . ";color:#" . $outfitObject->item1->text_color . "'>";
    formatAppSmallItem($userid, $outfitObject->item1);
    formatAppSmallItem($userid, $outfitObject->item2);
    formatAppSmallItem($userid, $outfitObject->item3);
    formatAppSmallItem($userid, $outfitObject->item4);
    formatAppSmallItem($userid, $outfitObject->item5);
    formatAppSmallItem($userid, $outfitObject->item6);
    echo "<span class='outfitName'><hr class='outfitLine'/>" . $outfitObject->name . "";
    if ($userid == $outfitObject->owner_id) {
        // allows you to edit outfit if you created it
        echo"</br><i class='fa fa-edit cursor editOutfitButton' onclick='editOutfit(" . $outfitObject->outfitid . ")'></i>";
    }
    echo "</span>";
    echo "</div>";
}

function formatOutfitItem($userid, $itemObject, $height = "", $itemLink = "") { // by default clicking directs to item 
    // this item has no user preview
    if ($itemObject->owner_id) {
        $owns_item = ($userid == $itemObject->owner_id);
        $item_tags = array();
        $tagmap_query = database_query("tagmap", "itemid", $itemObject->itemid);
        while ($tagmap = mysql_fetch_array($tagmap_query)) {
            $tag = database_fetch("tag", "tagid", $tagmap['tagid']);
            array_push($item_tags, $tag['name']);
        }
        $item_tags_string = implode(" #", $item_tags);
        if ($item_tags_string) {
            $item_tags_string = "#" . $item_tags_string;
        }
        if ($owns_item) {
            $purchaseString = "onclick=\"togglePurchaseLink(" . $itemObject->itemid . ")\"";
        } else {
            if ($itemObject->purchaselink) {
                $purchaseDisabled = "";
                $purchaseString = "href='" . $itemObject->purchaselink . "' target='_blank'";
            } else {
                $purchaseDisabled = " style='color:#808285;font-color:#808285;'";
                $purchaseString = "href='javascript:void(0)'";
            }
        }
        $search_string = str_replace("#", "%23", $item_tags_string);

        if ($itemLink == "off") {
            $itemLink = "";
        } else if (!$itemLink) { //
            $itemLink = "/hue/" . $itemObject->itemid;
        }
        $itemLink = "/hue/" . $itemObject->itemid;
        echo "<div class='outfitItemContainer' id='item" . $itemObject->itemid . "'style='color:#" . $itemObject->text_color . ";height:" . (($height) ? $height . "px;width:auto" : "") . "' >
        <img alt = '  This Image Is Broken' src = '" . $itemObject->image_link . "' onclick=\"Redirect('$itemLink')\" class = 'outfitImage' />
    <div class='outfitItemTagBox' style='background-color:#" . $itemObject->hexcode . ";'>
        <span class = 'outfitItemDesc' style='background-color:#" . $itemObject->hexcode . ";height:inherit'>" . stripslashes($itemObject->description) . "</span>
<input type = 'text' class='purchaseLink'  name = 'purchaseLink' onblur='hidePurchaseLink(" . $itemObject->itemid . ")' onchange = 'updatePurchaseLink(this, " . $itemObject->itemid . ")' value = '" . $itemObject->purchaselink . "' placeholder = 'link to buy/find item' />     
    </div>
    <br/>
</div>";
    }
}

function outfitUsers($outfitid) {
    // returns an array of all users who have items in the outfit

    $outfit = database_fetch("outfit", "outfitid", $outfitid);
    $useridArray = array();
    $itemsArray = array($outfit['itemid1'], $outfit['itemid2'], $outfit['itemid3'], $outfit['itemid4'], $outfit['itemid5'], $outfit['itemid6']);
    for ($i = 0; $i < count($itemsArray); $i++) {
        $item = database_fetch("item", "itemid", $itemsArray[$i]);
        if (!in_array($item['userid'], $useridArray)) {
            $useridArray[] = $item['userid'];
        }
    }
    return $useridArray;
}

?>
