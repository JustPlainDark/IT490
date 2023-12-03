<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Game News</title>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/bootstrap/css/bootstrap.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../Login_v5/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../Login_v5/fonts/Linearicons-Free-v1.0.0/icon-font.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/animate/animate.css">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/css-hamburgers/hamburgers.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/animsition/css/animsition.min.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/select2/select2.min.css">
	<!--===============================================================================================-->	
		<link rel="stylesheet" type="text/css" href="../Login_v5/vendor/daterangepicker/daterangepicker.css">
	<!--===============================================================================================-->
		<link rel="stylesheet" type="text/css" href="../css/news.css">
		<link rel="stylesheet" type="text/css" href="../css/main.css">
		<link rel="stylesheet" type="text/css" href="../css/util.css">
	<!--===============================================================================================-->

</head>

<body onload="getnews()">
	
<div class="navigationbar">
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

	  </div>




	<div class="limiter">
		<div class="container-login100" style="background-image: url('../Login_v5/images/bg-01.jpg');">
			<div class="wrap-login100 p-l-110 p-r-110 p-t-62 p-b-33">
					<span class="login100-form-title p-b-53">
						Game News
					</span>
						
					<script>
						function getnews(){
							<?php  

								require_once('../src/include/loginbase.inc'); 

								$client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
								session_start();
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

