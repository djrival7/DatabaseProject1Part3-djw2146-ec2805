<?php
$projname = $_GET["projname"];
ini_set('display_errors', 'On');
$db = "w4111b.cs.columbia.edu:1521/adb";
$conn = oci_connect("djw2146", "dudedude", $db);
$stmt = oci_parse($conn, "select email, description, date_created, user_email from projects where projname = '$projname'");
oci_execute($stmt, OCI_DEFAULT);
$project = oci_fetch_row($stmt);
$project_email = $project[0];
$project_desc = $project[1];
$project_date = $project[2];
$stmt = oci_parse($conn, "select name from users where email = '$project[3]'");
oci_execute($stmt, OCI_DEFAULT);
$owner = oci_fetch_row($stmt);
$owner_name = $owner[0];
$owner_email = $project[3];
$img_src = "images/1.png";
?>

<html>
    <head>
        <title>Project - <?php echo $projname; ?></title>

        <script type="text/javascript" src="javascripts/jquery-2.1.0.min.js"></script>
        <script type="text/javascript" src="javascripts/bootstrap.js"></script>
        <link rel="stylesheet" type="text/css" href="stylesheets/default.css" />
        <link rel="stylesheet" type="text/css" href="stylesheets/bootstrap-theme.css" />
        <link rel="stylesheet" type="text/css" href="stylesheets/bootstrap.css" />
        <style>
            .projinfo {
                margin-top: 50px;
                background-color: #CCDDAA;
                width:70%;
                margin-right:15%;
                margin-left:15%;
                padding: 5px;
            }
            
            .projinfo ul {
                display: inline;
                list-style-type: none;
            }
            
            .projinfo img {
                height: 30%;
            }
            
            .update {
                background-color: #888888;
                margin: 5px;
                padding: 5px;
            }

            #respond {
                margin-top: 40px;
            }

            #respond input[type='text'],
            #respond input[type='email'], 
            #respond textarea {
                margin-bottom: 10px;
                display: block;
                width: 100%;

                border: 1px solid rgba(0, 0, 0, 0.1);
                -webkit-border-radius: 5px;
                -moz-border-radius: 5px;
                -o-border-radius: 5px;
                -ms-border-radius: 5px;
                -khtml-border-radius: 5px;
                border-radius: 5px;

                line-height: 1.4em;
            }
        </style>

    </head>
    <body>
        <!-- <?php include ('php/navbar.php'); ?> -->

        <div class="content projinfo" id="projectInfo">
            <ul>         
                <li><h3><?php echo $projname; ?></h3></li>
                <li><img src="<?php echo $img_src; ?>" /></li>
            </ul>
            <?php echo "<h5>Owner: <a href='profile.php?email=$owner_email'>$owner_name</a></h5>";
            echo "<h5>Contact: $project_email</h5>";
            echo "<h5>Date created: $project_date</h5>";
            echo "<h5>Description: $project_desc</h5>";
            ?>
            <br />
            <h5>Publicity Links: </h5>
            <ul>
            <?php
            $stmt = oci_parse($conn, "select website_name, url from publicity_links where projname = '$projname'");
            oci_execute($stmt, OCI_DEFAULT);
            while ($pub_link = oci_fetch_row($stmt)) {
                echo "<li>$pub_link[0]: <a href='$pub_link'>$pub_link[1]</a></li>";

            }
            ?>
            </ul>
        <?php
        
        $stmt = oci_parse($conn, "select count(*) from likes where projname = '$projname'");
        oci_execute($stmt, OCI_DEFAULT);
        $num_likes = oci_fetch_row($stmt);
        ?>  
        <h5>Likes: <?php echo $num_likes[0];?></h5>
            <input type="button" onclick="" name="like" value="like"/>
        </div>
        
        <div class="projinfo">
            <h3>Updates</h3>
            <?php
            $stmt = oci_parse($conn, "select timestamp, content from updates where projname = '$projname'");
            oci_execute($stmt, OCI_DEFAULT);
            while ($update = oci_fetch_row($stmt)) { 
                    echo "<div class=\"update\">";
                    echo "<p>$update[0]</p>";
                    echo "<p>" . $update[1]->load() . "</p>";//Update content
                    echo "</div>";
                } 
            ?>
        </div>  <!-- updates -->

        <div class="projinfo">
            <h3>Comments</h3>
            <?php
            $stmt = oci_parse($conn, "select timestamp, content, user_email from comments where projname = '$projname'");
            oci_execute($stmt, OCI_DEFAULT);
            while ($comment = oci_fetch_row($stmt)) {
                    $stmt = oci_parse($conn, "select name from users where email = '$comment[2]'");
                    oci_execute($stmt, OCI_DEFAULT);
                    $user = oci_fetch_row($stmt);
                    echo "<div class=\"update\">";
                    echo "<p>$comment[0]</p>";
                    echo "<p>$comment[1]</p>";//comment content
                    echo "<a href='profile.php?email=$comment[2]'>$user[0]</p>";
                    echo "</div>";
                }
            ?>
        </div>  <!-- updates -->

        <div id="support_requests" class="projinfo">
            <h4>support requests</h4>
            <?php
                foreach($support_requests as $support_request) {
                    echo "<div>";
                    echo $support_request[0];
                    echo "</div>";
                }
?>
        </div>

        <div id="respond" class="projinfo">

            <h3>Leave a Comment</h3>

            <form action="post_comment.php" method="post" id="commentform">

                <label for="comment_author" class="required">Your name</label>
                <input type="text" name="comment_author" id="comment_author" value="" tabindex="1" required="required">

                <label for="email" class="required">Your email;</label>
                <input type="email" name="email" id="email" value="" tabindex="2" required="required">

                <label for="comment" class="required">Your message</label>
                <textarea name="comment" id="comment" rows="10" tabindex="4"	 required="required"></textarea>

                <-- comment_post_ID value hard-coded as 1 -->
                    <input type="hidden" name="comment_post_ID" value="1" id="comment_post_ID" />
                    <input name="submit" type="submit" value="Submit comment" />

                </form>

            </div>

            <?php include ('php/footer.php'); ?>
        </body>
    </html>
