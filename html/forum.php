<html>
    <head>
        <title> Game News </title>
        <!-- link to css stylesheet, has all the formatting--> 
		<link rel="stylesheet" href="../css/news.css">
	
    </head>

    <body onload="getnews()">

  <div class="navigationbar">
    <a href="index.html">Welcome</a>
    <a href="steamid.html">SteamID</a>
    <a href="profile.php"> Steam Profile</a>
    <a class="active" href="news.php">Game News</a>

    <div class="logout">
        <form method="POST" action="/html/loginbase.php">
            <div class="logoutarea">
                <input type="submit" name="logoutbutton" class="button" value="Log Out">
            </div>
        </form>
    </div>
  </div>

        <div class = "newsbox">
            <h1>Forum posts for "Monster Hunter: World":</h1> <!-- TODO: Make title dynamic to game in question. -->

            <script>
                function getposts(){
                    <?php  

                        require_once('../src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        session_start();
                        if($isset($_GET['gid']))
	                        $gameId = $_GET['gid'];
	                    else
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        $request = array(); 
                        $request['type'] = "forum_get_posts";
                        $request['gameID'] = $gid;
                        $request['page'] = 1;
                        
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

                <?php foreach($response['messages'] as $message) { ?>

                <div class="userinfo">      

                    <h4> <?php echo $message['username']; ?> said at <?php echo $message['postTime']; ?>:</h4>
                    <p><?php echo $message['message']; ?></p>

                </div>
                <?php } ?>
                </div>

    </body>
</html>
