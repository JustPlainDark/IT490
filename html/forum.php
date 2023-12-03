<html>
    <head>
        <title> Forum </title>
        <!-- link to css stylesheet, has all the formatting--> 
		<link rel="stylesheet" href="../css/news.css">
	
    </head>

<body onload="getposts()">

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
            <h1>Forum posts for "Monster Hunter: World":</h1> <!-- TODO: Make title dynamic to game in question. -->

            <script>
                function getposts(){
                    <?php  

                        require_once('../src/include/loginbase.inc'); 

                        $client = new rabbitMQClient("testRabbitMQ.ini","databaseServer"); 
                        session_start();
                        if(isset($_GET['gid'])) {
	                        $gameId = $_GET['gid'];
                        }
	                    else {
	                    	$gameId = '582010'; //Set to "Monster Hunter: World" for testing purposes.
                        }
                        $request = array(); 
                        $request['type'] = "forum_get_posts";
                        $request['gameID'] = $gameId;
                        $request['page'] = 1;
                        
                        $response = $client->send_request($request);

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
