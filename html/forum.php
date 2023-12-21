<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Forum Page</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="Login_v5/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="Login_v5/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/animate/animate.css">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/select2/select2.min.css">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="Login_v5/vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="css/news.css">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="stylesheet" type="text/css" href="css/util.css">
	<!--===============================================================================================-->
    <!-- Latest compiled and minified CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Latest compiled JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script> 
    <!--===============================================================================================-->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
	<!--===============================================================================================-->
</head>

<body onload="loadstuff()">
	
	<script><?php session_start(); ?></script>

	<nav class="navigationbar fixed-top">
		<ul class="nav-menu">
			<li class="nav-item">
				<a href="index.html" class="nav-link">Welcome</a>
			</li>
			<li class="nav-item">
				<a href="steamid.html" class="nav-link">Steam ID</a>
			</li>
			<li class="nav-item">
				<a href="profile.php" class="nav-link">Steam Profile</a>
			</li>
			<li class="nav-item">
				<a href="news.php" class="nav-link">News</a>
			</li>
            <li class="nav-item">
				<a href="games.php" class="nav-link">Games</a>
			</li>
		</ul>
		<div class="logout">
			<form method="POST" action="/html/loginbase.php">
				<div class="logoutarea">
					<input type="submit" name="logoutbutton" class="button" value="Log Out">
				</div>
			</form>
		</div>
		<div class="hamburger">
			<span class="bar"></span>
			<span class="bar"></span>
			<span class="bar"></span>
		</div>
	</nav>

	<script>
		const hamburger = document.querySelector(".hamburger");
		const navMenu = document.querySelector(".nav-menu");
		const navLink = document.querySelectorAll(".nav-link");

		hamburger.addEventListener("click", mobileMenu);

		function mobileMenu() {
			hamburger.classList.toggle("active");
			navMenu.classList.toggle("active");
		}

		navLink.forEach(n => n.addEventListener("click", closeMenu));

		function closeMenu() {
			hamburger.classList.remove("active");
			navMenu.classList.remove("active");
		}
	</script>


<div class="limiter">
		<div class="container-login100" style="background-image: url('Login_v5/images/bg-01.jpg');">
			<div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">
					<span class="login100-form-title p-b-53">
						Forums
					</span>
            <script>
            	function loadstuff(){
            	<?php
            			require_once('src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        if (isset($_POST['sendMessage']) && isset($_POST['message']) && isset($_SESSION['uid'])) {
            			
            			$userId = $_SESSION['uid'];
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
            			$message = $_POST['message'];
            			$postTime = time();
            			
                        //session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $request = array(); 
                        $request['type'] = "forum_add_post";
                        $request['gameID'] = $gameId;
                        $request['userID'] = $userId;
                        $request['message'] = $message;
                        $request['sendTime'] = $postTime;
                        
                        $postR = $client->send_request($request);
                        $censor = false;
                        if(isset($_GET['censor']) && $_GET['censor'] == 'true')
						{
							$censor = true;
						}
						echo 'if(confirm("Post sent succesfully!")){document.location.href="games.php"};';
                     //   header ("Refresh:0");
						header("games.php"); 
						//header("profile.php"); 

                    } else {
                    if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $censor = false;
                        if(isset($_GET['censor']) && $_GET['censor'] == 'true')
                        	$censor = true;
                        $request = array(); 
                        $request['type'] = "forum_get_posts";
                        $request['gameID'] = $gameId;
                        $request['page'] = 1;
                        $request['censor'] = $censor;
                        
                        $getR = $client->send_request($request);
						$problem = false;
						if($getR == false){
							$problem = true;
						}
						if(!$problem){
							$gameName = $getR['game'];
						}
                    }
            		?>
            	}

                function newpost(){
            		
            		<?php /*
                        if (isset($_POST['sendMessage']) && isset($_POST['message']) && isset($_SESSION['uid'])) {
            			
            			$userId = $_SESSION['uid'];
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
            			$message = $_POST['message'];
            			$postTime = time();
            			
            			require_once('src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        //session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $request = array(); 
                        $request['type'] = "forum_add_post";
                        $request['gameID'] = $gameId;
                        $request['userID'] = $userId;
                        $request['message'] = $message;
                        $request['sendTime'] = $postTime;
                        
                        $postR = $client->send_request($request);
                    }*/
            		?>
            	}
                function getposts(){
                    <?php  /*

                        require_once('src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        //session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $censor = false;
                        if(isset($_GET['censor']) && $_GET['censor'] == 'true')
                        	$censor = true;
                        $request = array(); 
                        $request['type'] = "forum_get_posts";
                        $request['gameID'] = $gameId;
                        $request['page'] = 1;
                        $request['censor'] = $censor;
                        
                        $getR = $client->send_request($request);
						$problem = false;
						if($getR == false){
							$problem = true;
						}
						if(!$problem){
							$gameName = $getR['game'];
						}*/
                   ?>
                }
            </script>

            <?php if(isset($problem) && $problem) { ?>
            <h4>Error: could not find game.</h4>
            <?php } else { ?>
            <h1>Forum posts for <em><?php echo $gameName; ?></em>  (<?php echo '<a href="forum.php?gid=', urlencode($gameId), '&censor=', $censor ? 'false">uncensor' : 'true">censor', '</a>'; ?>):</h1>

                <?php foreach($getR['messages'] as $message) { ?>

                <div class="limiter">      

                    <h4> <?php echo $message['username']; ?> said at <?php echo $message['postTime']; ?>:</h4>
                    <p><?php echo $message['message']; ?></p>
					<br>
                </div>
                <?php } ?>
                
                <?php if(!isset($_SESSION['uid'])) { ?>
                	<h2>(You must be logged in to make a post.)</h2>
                <?php } else { ?>
                	
					<form id="messageForm" method="POST" action="">
                		<textarea id="messageField" name="message" form="messageForm" placeholder="Say something about this game! Just remember to be respectful." rows="5" cols="25" maxlength="400" required></textarea>
                		<!--<input type="submit" name="sendMessage" value="Say it!">-->

						<button class="login100-form-btn" style="padding: 0px;">
                                <input type="submit" name="sendMessage" value="Enter" style="background-color: transparent; width: 100%; height: 100%;">
								
							</button> 
                	</form>


                <?php } }?>

			</div>
		</div>
</div>

    </body>
</html>
