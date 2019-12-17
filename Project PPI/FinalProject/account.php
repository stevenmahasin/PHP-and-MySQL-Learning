<?php
    require_once('checklog.php');
    require_once('db_connect.php');
    require_once('function.php');
    $username=$_SESSION['username'];
    $fullname=$_SESSION['fullname'];
    $db_email=$_SESSION['email'];
    $db_university=$_SESSION['university'];
    $db_course=$_SESSION['course'];
    $db_level=$_SESSION['level'];

    mysqli_select_db($db_server, $db_database);
    $query = "SELECT * FROM
    (SELECT reply_ID, post_ID, userID, comment, sentiment, dislike, commDate FROM replies 
    UNION ALL 
    -- -1 is used as the reply_ID it increments from 0 in the table 'replies', thus comments is always ordered first
    SELECT '-1' AS reply_ID, post_ID, userID, comment, sentiment, dislike, commDate FROM comments) AS content
    INNER JOIN
    Students ON content.userID = Students.ID 
    WHERE Students.ID = " . $_SESSION['userID'] . " 
    ORDER BY content.commDate, content.post_ID, content.reply_ID";

    $result = mysqli_query($db_server, $query);
    if (!$result) die("Database access failed: " . mysqli_error($db_server) );
    while($row = mysqli_fetch_array($result)){
        // Open divider per comment
        $comments .= "<div class = 'comments'><p>";
        if ($row['reply_ID'] != -1) {   // if it is a reply
            $comments .= "(reply) ";
        }
        $comments .= "<strong>" . $row['FullName'] . "</strong>";   // Fullname
        $comments .= "<em> (" . $row['Username'] . ")" . " - ";     // Username
        $comments .= $row['commDate'] . "</em></br>";               // Comment Date
        $comments .= $row['comment'] .    "<br/>";                  // Comment content

        // Like indicator
        if ($row['reply_ID'] == -1) {   // if it is a comment
            if(!isset($_SESSION["liked_" . $row['post_ID']])){ // Comment is not liked yet
                $comments .= "<a><i class='fa fa-thumbs-up' style='color:grey'></i></a>&nbsp" . $row['sentiment'] . "&nbsp &nbsp";
            }else{
                $comments .= "<i class='fa fa-thumbs-up' style='color:green'></i>&nbsp" . $row['sentiment'] . "&nbsp &nbsp";
            }
            if(!isset($_SESSION["disliked_" . $row['post_ID']])){ // Comment is not disliked yet
                $comments .= "<a><i class='fa fa-thumbs-down' style='color:grey'></i></a>&nbsp" . $row['dislike'];
            }else{
                $comments .= "<i class='fa fa-thumbs-down' style='color:#e6300c'></i>&nbsp" . $row['dislike'];
            }
        } else {
            if(!isset($_SESSION["reply_liked_" . $row['reply_ID']])){ // Comment is not liked yet
                $comments .= "<a><i class='fa fa-thumbs-up' style='color:grey'></i></a>&nbsp" . $row['sentiment'] . "&nbsp &nbsp";
            }else{
                $comments .= "<i class='fa fa-thumbs-up' style='color:green'></i>&nbsp" . $row['sentiment'] . "&nbsp &nbsp";
            }
            if(!isset($_SESSION["reply_disliked_" . $row['reply_ID']])){ // Comment is not disliked yet
                $comments .= "<a><i class='fa fa-thumbs-down' style='color:grey'></i></a>&nbsp" . $row['dislike'];
            }else{
                $comments .= "<i class='fa fa-thumbs-down' style='color:#e6300c'></i>&nbsp" . $row['dislike'];
            }
        }

        // Reply button
        if ($row['reply_ID'] == -1) {
            $comments .= "&nbsp | &nbsp" . "<a class='forum-button' href='reply.php?postid=" . $row['post_ID'] . "'><i class='fa fa-reply'></i>   Reply</a>   ";
        } else {
            $comments .= "&nbsp | &nbsp" . "<a class='forum-button' href='reply.php?postid=" . $row['post_ID'] . "'>   Go to main comment</a>   ";
        }

        // Delete Button
        $comments .="&nbsp | &nbsp" . "<a class='forum-button' href='delete_post.php?pID=" . $row['post_ID'] . "&previousURL=forum.php'><i class='fa fa-trash'></i>   Delete</a>   ";
        
        // Close divider per comment
        $comments .= "</p><hr/></div>";
    }
    mysqli_free_result($result);
    mysqli_close($db_server); 
?>
<html>
	<head>
		<meta charset="utf-8">
		<title>Leeds Indonesian Student Association</title>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css?family=Montserrat:800&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel="stylesheet" href="stylesheet.css">
	</head>
	<body>
    	<div id="wrapper">
            <div id="main">
                <?php require_once('header_logged.php')?>
                
                <div class="hero">
                    <img src="images/hero-home.jpg" alt="Indonesian Student Association" width="100%">
                </div>

                <div class="main-info"> 
                    <h1><strong>Hello,  <?php echo $fullname; ?></strong></h1>
                    <p>Welcome to your account. You can manage your info, privacy, and security.</p>
                    
                    <div class="account-details-1">
                        <h3>Name:</h3>
                        <p><?php echo $fullname; ?></p>

                        <h3>Email:</h3>
                        <p><?php echo $db_email; ?></p>

                        <h3>Username:</h3>
                        <p><?php echo $username ?></p>

                    </div>
                    
                    <div class="account-details-2">
                        
                        <h3>University:</h3>
                        <p><?php echo $db_university; ?></p>

                        <h3>Level:</h3>
                        <p><?php echo $db_level; ?></p>

                        <h3>Course:</h3>
                        <p><?php echo $db_course; ?></p>
                    </div>
                    
                    <div class="account-links">
                        <h4><a href="edit_details.php">Edit your Details</a></h4>
                        <h4><a href="change_password.php">Change Password</a></h4>
                        <br/>
                        <h4><a href="delete.php">Delete Account</a></h4>
                        <h4><a href="logout.php">Log Out</a></h4>
                        <br/><br/><br/><br/><br/>
                    </div>
                    <hr>
                    <h1>Your Comments: </h1>
                    <p><?php echo $comments; ?></p>
                </div>
                
                <div id="footer">
                    <p class="footer">© 2019 <a class="footer-link" href="http://www.corinagunawidjaja.myportfolio.com">Corina Gunawidjaja</a>. All Rights Reserved.</p>
                 </div>
            </div>

        </div>
    </body>
</html>


