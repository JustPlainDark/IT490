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
        <a class="active" href="games.php">My Games</a>

        <div class="logout">
            <form method="POST" action="/html/loginbase.php">
                <div class="logoutarea">
                    <input type="submit" name="logoutbutton" class="button" value="Log Out">
                </div>
            </form>
        </div>
    </div>

        <script>
        	function loadstuff(){
	            <?php  
					if(!isset($_SESSION['uid']))
						$problem = true;
					else{
					    require_once('../src/include/loginbase.inc'); 

					    $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
					    //session_start();
					    $request = array(); 
					    $request['type'] = "user_get_games";
					    $request['userID'] = $_SESSION['uid'];
					    
					    $games = $client->send_request($request);
						$problem = false;
						if($games == false){
							$problem = true;
						}
					}
	        	?>
        	}
        </script>
        
        <?php if((isset($problem) && $problem) || count($games) == 0) { ?>
        <div class = "newsbox">
        	<h4>Error: could not find any games.</h4>
        </div>
        <?php } else { ?>
        
        <?php foreach($games as $game) { ?>
        <div class = "newsbox">
            <h2><?php echo $game['gname']; ?></h2>
            <p><?php echo '<a href="forum.php?gid=', urlencode($game['gid']), '&censor=false">Forum</a>        <a href="review.php?gid=', urlencode($game['gid']), '&censor=false">Reviews</a>'; ?></p>
        </div>
		<?php }} ?>
    </body>
</html>
