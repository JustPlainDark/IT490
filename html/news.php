<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Game News</title>
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

<body onload="getnews()">
<script> <?php session_start(); ?> </script>


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

<!-- <div class="navigationbar">
		<a href="index.html">Welcome</a>
		<a href="steamid.html">SteamID</a>
		<a href="profile.php">Steam Profile</a>
		<a href="news.php">Game News</a>


		<div class="logout">
			<form method="POST" action="/html/loginbase.php">
				<div class="logoutarea">
					<input type="submit" name="logoutbutton" class="button" value="Log Out">
				</div>
			</form>
		</div>

	  </div>-->


	<div class="limiter">
		<div class="container-login100" style="background-image: url('Login_v5/images/bg-01.jpg'); padding-top: 5%;">
			<div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">
					<span class="login100-form-title p-b-53">
						Game News
					</span>
						
					<script>
						function getnews(){
							<?php  

								require_once('src/include/loginbase.inc'); 

								$client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
								$uid = $_SESSION['uid'];
								$request = array(); 
								$request['type'] = "get_user_news";
								$request['userId'] = $uid;
								
								$response = $client->send_request($request);
				
							// $game = array($response['game']);
							// $title = array($response['title']);
							// $link = array($response['link']);
							// $game = $response[0]['game'];
							// $title = $response[0]['title'];
							// $link = $response[0]['link'];

							?>
						}
					</script>

					<?php foreach($response as $game) { ?>


						<p> Game Name: <?php echo $game['game']; ?> </p>
						<p> Article Title: <?php echo $game['title']; ?> </p>
						<p> Link to Article: <?php echo $game['link']; ?> </p>

					<?php } ?>

				</div>	
			</div>
		</div>
	</div>

</body>
</html>

