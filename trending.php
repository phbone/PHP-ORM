<?php
session_start();
include('connection.php');
include('global_tools.php');
include('global_objects.php');
include('database_functions.php');
include('algorithms.php');
$userid = $_SESSION['userid'];
?>
<html>
    <head>
        <?php initiateTools() ?>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script type="text/javascript" src="/js/global_javascript.js"></script>
        <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
        <script src="http://code.jquery.com/jquery-1.9.1.js"></script>
        <link rel="stylesheet" href="/fancybox/source/jquery.fancybox.css?" type="text/css" media="screen" />
        <script type="text/javascript" src="/fancybox/source/jquery.fancybox.pack.js?"></script>
        <script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
        <link rel="stylesheet" type="text/css" href="/css/global.css" />
    </head>
    <body>
        <img src="/img/loading.gif" id="loading"/>
        <?php commonHeader(); ?>
        <div id="mainContainer">

            <div id="topLabel"><span id="topText">Trending Tags</span></div>

            <div id="topContainer" style="top:210px;">
                <div id="followers" class="previewContainer">
                    <br/>
                    <div class="linedTitle">
                        <span class="linedText">
                            Tags
                        </span>
                    </div>
                    <br/>
                    <?php
                    $trendingItems = array();
                    $tagNames = array();
                    $tagsQuery = "SELECT * FROM tag ORDER BY count DESC LIMIT 10";
                    $tagsResult = mysql_query($tagsQuery);
                    while ($tag = mysql_fetch_array($tagsResult)) {
                        echo "#" . $tag['name'] . "<br/>";
                        $tagNames[] = $tag['name'];
                        $trendingItems[] = $tag['tagid']; // get the tagid of the 10 most popular tags
                    }
                    ?>
                </div>
            </div>
            <div id="itemBackground">
                <div class="divider">
                    <hr class="left"/>
                    <span id="mainHeading">THE HIVE</span>
                    <hr class="right" />
                </div>
                <?php
                for ($i = 0; $i < 10; $i++) {
                    $tagmapQuery = "SELECT * FROM tagmap WHERE tagid = '" . $trendingItems[$i] . "' ORDER BY tagmapid DESC LIMIT 10";
                    $tagmapResult = mysql_query($tagmapQuery);
                    while ($tagmap = mysql_fetch_array($tagmapResult)) {

                        echo "<div class='" . $tagNames[$i] . "'>";
                        $item_object = returnItem($tagmap['itemid']);
                        formatItem($userid, $item_object);
                        echo "</div>";
                    }
                }
                ?>
            </div>
        </div>
    </body>
</html>