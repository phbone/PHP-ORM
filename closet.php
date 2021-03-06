<?php
session_start();
include('connection.php');
include('database_functions.php');

$userid = $_SESSION['userid'];
$user = database_fetch("user", "userid", $userid);
// your userid
$owner_username = $_GET['username'];
$owner = database_fetch("user", "username", $owner_username);
$closet_owner = $owner['userid'];
//// userid of the person whose closet your trying to see

$owns_closet = ($userid == $closet_owner);
$item_count = $owner['itemcount'];
$useridArray[] = $owner['userid'];
$view = $_GET['view'];


include('global_tools.php');
include('global_objects.php');
$size = getimagesize($owner['picture']);
?>
<!DOCTYPE html>
<html>
    <head>
        <?php initiateTools() ?>
        <link rel="stylesheet" type="text/css" href="/css/closet.css" />
        <script type="text/javascript" >

<?php initiateTypeahead(); ?>
<?php checkNotifications(); ?>

            var userid = "<?php echo $userid ?>";
            var useridArray = <?php echo json_encode($useridArray) ?>;
            var itemOffset = 0;
            var outfitOffset = 0;
            var limit = 5;
            var paginateOutfit = "1";
            var paginateItem = "1";

            $(document).ready(function(e) {
                bindActions();
                initiatePagination(useridArray);
                $('#filterInput').keyup(function() {
                    filterItems($('#filterInput').val())
                });
                flipView('<?php echo $view ?>');
            });


            function flipRequest(id) {
                if (id == "followers") {
                    $("#followers").fadeIn();
                    $("#following").hide();
                    $("#top").hide();
                }
                else if (id == "following") {
                    $("#following").fadeIn();
                    $("#followers").hide();
                    $("#top").hide();
                }
                else if (id == "top") {
                    $("#top").fadeIn();
                    $("#following").hide();
                    $("#followers").hide();
                }
            }
            function flipView(id) {
                // switches to item or outfits
                if (id == "closet") {
                    $("#itemBackground").fadeIn();
                    $("#outfitBackground").fadeOut();
                }
                else if (id == "outfit") {
                    $("#itemBackground").fadeOut();
                    $("#outfitBackground").fadeIn();
                    outfitPagination('outfit', useridArray);

                }
            }
            function editMode() {
                $(".trashIcon").toggle();
                $("#editText").toggleClass("active");
                $(".editIcon").css("display", "block");
            }


        </script>
    </head>
    <body>
        <?php initiateNotification() ?>
        <?php commonHeader() ?>
        <img src="/img/loading.gif" id="loading"/>
        <div class="mainContainer">
            <?php
            $share_text = $owner['name'] . "%27s%20closet%20on%20hueclues";
            if ($owns_closet) {
                $share_text = "My%20closet%20on%20hueclues";
            }
            ?>
            <div class="selfContainer">
                <img class='selfPicture' src="<?php echo $owner['picture']; ?>" <?php
                if ($owns_closet) {
                    echo "onclick='Redirect(\"/account\")'";
                }
                ?> ></img>
                <span class="selfName">
                    <?php echo $owner['username'] . " - " . $owner['name']; ?>
                </span>
                <span class="selfBio">
                    <?php echo $owner['bio']; ?>
                </span>
                <br/>
                <div id="follow_nav">
                    <div class="selfDetail">
                        <span class="selfCount" id="following_btn" onclick="flipView('closet')"><?php echo $owner['itemcount']; ?>
                        </span>
                        <br/>items 
                    </div>
                    <div class="selfDetail">
                        <span class="selfCount" id="follower_btn" onclick="flipView('outfit')"><?php echo $owner['outfitcount']; ?>
                        </span>
                        <br/>outfits
                    </div>
                    <div class="selfDetail">
                        <span class="selfCount" id="following_btn" onclick="flipRequest('following')"><?php echo $owner['following']; ?>
                        </span>
                        <br/>following 
                    </div>
                    <div class="selfDetail">
                        <span class="selfCount" id="follower_btn" onclick="flipRequest('followers')"><?php echo $owner['followers']; ?>
                        </span>
                        <br/>followers
                    </div>
                </div>
                <br/>
                <?php
                if ($owns_closet) {
                    echo "<a href='/extraction'><button id='uploadItem' class='greenButton'>CREATE ITEM &nbsp<img class='buttonImage' src='/img/camera.png'></img></button></a>";
                } else {
                    echo "<button id='followaction" . $owner['userid'] . "' class='closetFollow greenFollowButton " . ((database_fetch("follow ", "userid", $owner['userid'], "followerid", $userid)) ? 'clicked' : '') . "' 
                    onclick='followButton(" . $owner['userid'] . ")'>" . ((database_fetch("follow ", "userid", $owner['userid'], "followerid", $userid)) ? "following" : "follow") . "</button>";
                }
                ?>
            </div>



            <div id="topContainer">
                <div id="followers" class="previewContainer">
                    <br/>
                    <div class="linedTitle">
                        <span class="linedText">
                            Followers
                        </span>
                    </div>
                    <br/>
                    <?php
                    $follower_query = database_query("follow", "userid", $closet_owner);
                    while ($follower = mysql_fetch_array($follower_query)) {
                        formatUser($userid, $follower['followerid']);
                    }
                    ?>
                </div>
                <div id="following" class="previewContainer" style="display:none;">
                    <br/>
                    <div class="linedTitle">
                        <span class="linedText">
                            Following
                        </span>
                    </div>
                    <br/>
                    <?php
                    $following_query = database_or_query("follow ", "followerid", $closet_owner);
                    while ($following = mysql_fetch_array($following_query)) {
                        // shows who your closet is connected with
                        formatUser($userid, $following['userid']);
                    }
                    ?>
                </div>
            </div> 
            <div id="itemBackground"> 
                <div class="divider">
                    <hr class="left" style="width:38%;"/>
                    <span id="mainHeading"><?php
                        // $other user refers to the person who you are trying to view
                        $other_user = database_fetch("user ", "userid", $closet_owner);
                        if ($other_user) {
                            echo "CLOSET";
                        } else {
                            echo "INVALID";
                        }
                        ?></span>
                    <hr class="right" style="width:38%;"/>
                </div>
                <div id="shareContainer">
                    <a onclick="window.open('http://www.facebook.com/sharer.php?u=http://hueclues.com/closet/<?php echo $owner_username; ?>', 'newwindow', 'width=550, height=400')" href="#">                    
                        <img class="shareIcon" src="/img/shareFacebook.png" ></img></a>
                    <a onclick="window.open('http://twitter.com/share?text=<?php echo $share_text . "&url=http://hueclues.com/closet/" . $owner_username; ?>', 'newwindow', 'width=550, height=400')" href="#">
                        <img class="shareIcon" src="/img/shareTwitter.png" ></img></a>
                    <span onclick='editMode()' id='editText'>|edit</span>
                </div>
                <?php
                if ($owns_closet) {
                    
                }
                ?>
                <input type='text' id='filterInput' placeholder="search items: #shirt"></input>
                <br/><br/>
                <?php
                if ($owns_closet && $item_count == 0) {
                    echo "<a href='/upload' style='text-decoration:none;'><span class='messageGreen'>This is where your items are showcased. Add some now</span></a>";
                }
                ?>          

                <button id="loadMore" class="greenButton"  onclick="itemPagination(useridArray);">Load More...</button>

            </div>

            <div id="outfitBackground" style='display:none;'> 

                <?php
                if ($owns_closet) {
                    echo "<button id = 'createOutfitButton' class = 'greenButton bigButton' onclick = 'createOutfit()'>Create New Outfit</button><br/>";
                }
                ?>

                <button id="loadMore" class="greenButton"  onclick="outfitPagination('outfit', useridArray);">Load More...</button>

            </div>

        </div>
    </body>
</html>