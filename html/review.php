<html>
    <head>
        <title> Forum </title>
        <!-- link to css stylesheet, has all the formatting--> 
		<link rel="stylesheet" href="../css/news.css">
	
    </head>

<body onload="loadstuff()">
	
	<script><?php session_start(); ?></script>
	
    <div class="navigationbar">
        <a href="index.html">Welcome</a>
        <a href="steamid.html">SteamID</a>
        <a href="profile.php"> Steam Profile</a>
        <a href="news.php">Game News</a>
        <a class="active" href="forum.php">Forum</a>

        <div class="logout">
            <form method="POST" action="/html/loginbase.php">
                <div class="logoutarea">
                    <input type="submit" name="logoutbutton" class="button" value="Log Out">
                </div>
            </form>
        </div>
    </div>

        <div class = "newsbox">
            <script>
            	function loadstuff(){
            	<?php
        			require_once('../src/include/loginbase.inc'); 

                    $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
            		if (isset($_POST['makeReview']) && isset($_POST['message']) && isset($_SESSION['uid'])) {
            			
            			$userId = $_SESSION['uid'];
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
            			$message = $_POST['message'];
            			$postTime = time();
            			if($_POST['posneg'] == "pos")
            				$positive = 1;
            			else
            				$positive = 0;
            			
                        //session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $request = array(); 
                        $request['type'] = "review_add_post";
                        $request['gameID'] = $gameId;
                        $request['userID'] = $userId;
                        $request['message'] = $message;
                        $request['positive'] = $positive;
                        
                        $postR = $client->send_request($request);
                    }
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
                        $request['type'] = "review_get_posts";
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
					?>
            	}
            	function newpost(){
            		
            		<?php/*
                        if (isset($_POST['makeReview']) && isset($_POST['message']) && isset($_SESSION['uid'])) {
            			
            			$userId = $_SESSION['uid'];
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
            			$message = $_POST['message'];
            			$postTime = time();
            			if($_POST['posneg'] == "pos")
            				$positive = 1;
            			else
            				$positive = 0;
            			
            			require_once('../src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        //session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $request = array(); 
                        $request['type'] = "review_add_post";
                        $request['gameID'] = $gameId;
                        $request['userID'] = $userId;
                        $request['message'] = $message;
                        $request['positive'] = $positive;
                        
                        $postR = $client->send_request($request);
                    }*/
            		?>
            	}
                function getposts(){
                    <?php  /*

                        require_once('../src/include/loginbase.inc'); 

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
                        $request['type'] = "review_get_posts";
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
            <h1>Reviews for <em><?php echo $gameName; ?></em>  (<?php echo '<a href="review.php?gid=', urlencode($gameId), '&censor=', $censor ? 'false">uncensor' : 'true">censor', '</a>'; ?>):</h1> <!-- TODO: Make title dynamic to game in question. -->

                <?php foreach($getR['messages'] as $message) { ?>

                <div class="userinfo">      

                    <h4><?php echo $message['username'] . ($message['positive'] == '1' ? " " : " dis") . "likes this game!"; ?></h4>
                    <p><?php echo $message['message']; ?></p>
					<br>
                </div>
                <?php } ?>
                
                <?php if(!isset($_SESSION['uid'])) { ?>
                	<h2>(You must be logged in to make a review.)</h2>
                <?php } else { ?>
                	<form id="messageForm" method="POST" action="">
                		<input type="radio" id="pos" name="posneg" value="pos" required>
                		<label for="pos">I enjoyed this game!</label><br>
                		<input type="radio" id="neg" name="posneg" value="neg" required>
                		<label for="neg">I did not enjoy this game!</label><br>
                		<textarea id="messageField" name="message" form="messageForm" placeholder="Give your honest opinion! Just remember to be respectful." rows="80" cols="5" maxlength="400" required></textarea>
                		<input type="submit" name="makeReview" value="Say it!">
                	</form>
                <?php } }?>
        </div>

    </body>
</html>
