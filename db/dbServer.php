#!/usr/bin/php
<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');
$db = new mysqli('127.0.0.1','dbManager','shackle','mainDB');

if ($db->errno != 0)
{
	echo "failed to connect to database: ". $mydb->error . PHP_EOL;
	exit(0);
}
echo "successfully connected to database".PHP_EOL;

////	LOGIN/REGISTER FUNCS	////

function doLogin($username,$password,$sessid)
{
    global $db;
    $query = "select * from Users where username='{$username}'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return array("result"=>'0',"msg"=>"Error executing query.");
	}
    if ($sqlResponse->num_rows == 0){
    	$errmsg = "Username not found.";
    	return array("result"=>'0',"msg"=>$errmsg);
    }
    $row = $sqlResponse->fetch_assoc();
    if(!password_verify($password, $row['password'])){
    	$errmsg = "Incorrect password.";
    	return array("result"=>'0',"msg"=>$errmsg);
    }
    
    if($row['steamID'] != NULL){
		$importedData = steam_getUserData($row['userid'], $row['steamID']);
		if($importedData)
			echo "Successfully imported user info.".PHP_EOL;
		else
			echo "Unsuccessfully imported user info.".PHP_EOL;
    }
    $sqlResponse->close();
    $db->next_result();
    
    $success = createSession($row["userid"], $sessid);
    if ($success)
    	return array("result"=>'1',"uid"=>$row["userid"]);
    return array("result"=>'0',"msg"=>"Error registering session.");
}
function doRegister($username, $password, $email){
	global $db;
	$hash = password_hash($password, PASSWORD_DEFAULT);
	$query = "insert into Users (username, email, password) values ('{$username}', '{$email}', '{$hash}')";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    //$sqlResponse->close();
    $db->next_result();
	
	return true;
}
/////////
function createSession($uid, $sessid){
	global $db;
	$query = "insert into Sessions (sessionid, userid) values ('{$sessid}','{$uid}')";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    //$sqlResponse->close();
    $db->next_result();
	
	return true;
}
/////////
function validateSession($sessid){
	global $db;
	$query = "select * from Sessions where sessionid='{$sessid}'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    if ($sqlResponse->num_rows == 0){
    	//session does not exist.
    	$sqlResponse->close();
    	$db->next_result();
    	return false;
    }
    $row = $sqlResponse->fetch_assoc();
	if($row["isactive"] == 0){
    	//session has already been logged out.
		$sqlResponse->close();
		$db->next_result();
    	return false;
    }
    $sqlResponse->close();
    $db->next_result();
	return true;
}
/////////
function logout($sessid){
	global $db;
	$query = "update Sessions set isactive='0' where sessionid='{$sessid}'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	return true;
}

////	USER STEAM FUNCS	////

function steam_getUserData($userid, $steamid){
	global $db;
	
	$query = "select lastSync from SteamUsers where userID='{$userid}'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	$sync = 0;
	if($sqlResponse->num_rows > 0){
		$row = $sqlResponse->fetch_assoc();
		$sync = strtotime($row['lastSync']);
		if(time() - $sync < 300){
			echo "Refusing to sync, it has not been 5 minutes.".PHP_EOL;
			$sqlResponse->close();
			$db->next_result();
			return false;
		}
	}
    $sqlResponse->close();
    $db->next_result();
	
	$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
	$request = array();
	$request['type'] = "get_steam_profile";
	$request['id'] = $steamid;
	$response = $client->send_request($request);
	
	//unset($client);
	
	
	if($response == 0)
		return false;
	
	$un = $response['username'];
	$av = $response['avatar'];
	
	$query = "insert into SteamUsers (userID, steamName, avatar) values ('{$userid}', '{$un}', '{$av}') on duplicate key update steamName='{$un}', avatar='{$av}', lastSync = current_timestamp";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	if(isset($response['library'])){
		$gamesAdded = array();
		$ulib = $response['library'];
		$query = "";
		$index = 0;
		foreach($ulib as $game){
			$gid = $game['appid'];
			array_push($gamesAdded, $gid);
			$pt = $game['playtime'];
			$query .= "insert into UserGames (userID, gameID, playTime) values ('{$userid}', '{$gid}', '{$pt}') on duplicate key update playTime='{$pt}'";
			$index += 1;
			if($index < sizeof($ulib))
				$query .= "; ";
		}
		
		$db->multi_query($query);
		
		if ($db->errno != 0)
		{
			echo "failed to execute query:".PHP_EOL;
			echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		}
    	do{
    		if ($result = $db->store_result()) {
        		var_dump($result->fetch_all(MYSQLI_ASSOC));
        		$result->free();
    		}
    	} while ($db->next_result());
    	/*
    	$query = "";
    	$index = 0;
		foreach($gamesAdded as $game){
			$query .= "select name from Games where appid='{$game}'";
			$index += 1;
			if($index < sizeof($gamesAdded))
				$query .= "; ";
		}
		$db->multi_query($query);
		
		if ($db->errno != 0)
		{
			echo "failed to execute query:".PHP_EOL;
			echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		}
    	do{
    		if ($result = $db->store_result()) {
        		var_dump($result->fetch_all(MYSQLI_ASSOC));
        		$result->free();
    		}
    	} while ($db->next_result());
    	*/
	}
	
	if(time() - $sync >= 3600){
		steam_updateLibrary($userid);
	}
	
	return true;
}
/////////
function steam_updateLibrary($userid){
	global $db;
	$query = "select distinct gameID from UserGames where userID='{$userid}' and gameID not in (select appid from Games)";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	}
	
	if($sqlResponse->num_rows == 0){
		echo "All games exist in database.";
		return;
	}
	$ids = array();
	while($row = $sqlResponse->fetch_assoc()){
		$ids[] = $row['gameID'];
	}
	$sqlResponse->close();
    $db->next_result();
	
	$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
	$request = array();
	$request['type'] = "get_app_info";
	$request['ids'] = $ids;
	$response = $client->send_request($request);
	if($response == 0) return;
	$query = "";
	foreach($response as $arr){
		$appid = $arr['appid'];
		$name = mysqli_real_escape_string($db, $arr['name']);
		$developer = mysqli_real_escape_string($db, $arr['developer']);
		$price = $arr['price'];
		$price = $price / 100.0;
		$genre = mysqli_real_escape_string($db, $arr['genre']);
		$tags = mysqli_real_escape_string($db, $arr['tags']);
		$query .= "insert into Games (appid, name, developer, price, genre, tags) values ('{$appid}','{$name}','{$developer}','{$price}','{$genre}','{$tags}'); ";
	}
	$db->multi_query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	}
	do{
		if ($result = $db->store_result()) {
    		var_dump($result->fetch_all(MYSQLI_ASSOC));
    		$result->free();
		}
	} while ($db->next_result());
	
	return;
}
/////////
function steam_giveUserData($userid){
	global $db;
	$query = "select steamName, avatar from SteamUsers where userID='{$userid}'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	if ($sqlResponse->num_rows == 0){
		echo "Invalid userID or user has not set up Steam Link.";
		$sqlResponse->close();
		$db->next_result();
		return false;
	}
	$row = $sqlResponse->fetch_assoc();
	$response = array('steamName' => $row['steamName'], 'avatarLink' => $row['avatar']);
    $sqlResponse->close();
    $db->next_result();
	return $response;
}
/////////
function steam_setlink($sessid, $steamid){
	global $db;
	$query = "select userID from Sessions where sessionid='{$sessid}' and isactive='1'";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
    if ($sqlResponse->num_rows == 0){
    	//session does not exist.
		echo "Returning false, since it was not a valid sessionID.".PHP_EOL;
    	return false;
    }
    $row = $sqlResponse->fetch_assoc();
    $uid = $row["userID"];
    $sqlResponse->close();
    $db->next_result();
	
	//check if id is valid
	$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
	$request = array();
	$request['type'] = "check_steam_id";
	$request['id'] = $steamid;
	$response = $client->send_request($request);
	
	//unset($client);
	
	
	var_dump($response);
	
	if($response == 0){
		echo "Returning false, since it was not a valid SteamID.".PHP_EOL;
		return false;
	}
    
    //put in database if valid
    $query = "update Users set steamID='{$steamid}' where userid='{$uid}'";
    $sqlResponse = $db->query($query);
    
    if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	$importedData = steam_getUserData($uid, $steamid);
	if($importedData)
		echo "Successfully imported user info.".PHP_EOL;
	else
		echo "Unsuccessfully imported user info.".PHP_EOL;
		
	echo "Returning true.".PHP_EOL;
	
	return true;
}
/////////
function steam_getNews($userid){
	global $db;
	$query = "select distinct gameID, playTime from UserGames where playTime > '0' and userID = '{$userid}' order by playTime desc limit 5";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	}
	
	if($sqlResponse->num_rows == 0){
		echo "User has no played games.".PHP_EOL;
		return false;
	}
	
	$selectedGames = array();
	while($row = $sqlResponse->fetch_assoc()){
		$selectedGames[] = $row['gameID'];
	}
    $sqlResponse->close();
    $db->next_result();
    
	$qarr = '(' . implode(',',$selectedGames) . ')';
	$query = "select appid, lastSync from GameNews where appid in {$qarr}";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	}
	$newsToUpdate = array();
	if($sqlResponse->num_rows == 0){
		$newsToUpdate = $selectedGames;
	}
	else{
		$oldNews = array();
		while($row = $sqlResponse->fetch_assoc()){
			foreach($selectedGames as $item){
				if($item == $row['appid']){
					if(time() - strtotime($row['lastSync']) > 86400)
						$newsToUpdate[] = $row['appid'];
					$oldNews[] = $row['appid'];
					break;
				}
			}
		}
		foreach($selectedGames as $item){
			if(!in_array($item, $oldNews))
				$newsToUpdate[] = $item;
		}
	}
    $sqlResponse->close();
    $db->next_result();
	
	if(count($newsToUpdate) > 0){
		$client = new rabbitMQClient("testRabbitMQ.ini","dmzServer");
		$request = array();
		$request['type'] = "get_app_news";
		$request['ids'] = $newsToUpdate;
		$dmzResponse = $client->send_request($request);
		
		//UPDATE TABLE HERE.
		$query = "";
		foreach($dmzResponse as $arr){
			$title = mysqli_real_escape_string($db, $arr['title']);
			$link = $arr['link'];
			$id = $arr['appid'];
			$query .= "insert into GameNews (appid, title, link) values ('{$id}', '{$title}', '{$link}') on duplicate key update title='{$title}', link='{$link}', lastSync = current_timestamp; ";
		}
		$db->multi_query($query);
		
		if ($db->errno != 0)
		{
			echo "failed to execute query:".PHP_EOL;
			echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		}
		do{
			if ($result = $db->store_result()) {
				var_dump($result->fetch_all(MYSQLI_ASSOC));
				$result->free();
			}
		} while ($db->next_result());
	}
	$qarr = '(' . implode(',',$selectedGames) . ')';
	echo $qarr.PHP_EOL;
	//$query = "select distinct Games.name as game, GameNews.title as title, GameNews.link as link, GameNews.lastSync, UserGames.playTime from ((Games join GameNews on Games.appid = GameNews.appid) join UserGames on Games.appid = UserGames.gameID) where Games.appid in {$qarr} order by UserGames.playTime desc";
	$query = "select distinct Games.name as game, GameNews.title as title, GameNews.link as link, GameNews.lastSync from (Games join GameNews on Games.appid = GameNews.appid) where Games.appid in {$qarr}";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
	}
	$response = array();
	
	while($row = $sqlResponse->fetch_assoc()){
		$response[] = array('game'=>$row['game'],'title'=>$row['title'],'link'=>$row['link']);
	}
	echo "Responding with ".count($response)." items of news:".PHP_EOL;
    $sqlResponse->close();
    $db->next_result();
	return $response;
}

////	API STEAM FUNCS		////
/*
function refresh_steamtopgames($arr){
	global $db;
	$query = "delete from DailySteamTopGames";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	
	$query = "";
	foreach($arr as $game){
		$appid = $arr['appid'];
		$name = $arr['name'];
		$developer = $arr['developer'];
		$price = $arr['price'];
		$price = $price / 100.0;
		$genre = $arr['genre'];
		$tags = $arr['tags'];
		$query .= "insert into DailySteamTopGames (appid, name, developer, price, genre, tags) values ('{$appid}','{$name}','{$developer}','{$price}','{$genre}','{$tags}'); ";
	}
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	echo "Returning true.".PHP_EOL;
	return true;
}
*/

////	FORUM/REVIEW FUNCS		////
function getUserGameList($userid, $limit){
	global $db;
	$query = "select Games.name as name, Games.appid as gid, UserGames.gameID, UserGames.playTime from (UserGames join Games on UserGames.gameID = Games.appid) where UserGames.playTime > '0' and UserGames.userID = '{$userid}' order by UserGames.playTime desc limit {$limit};";
	$sqlResponse = $db->query($query);
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	if($sqlResponse->num_rows == 0){
		return false;
	}
	$response = array();
	
	while($row = $sqlResponse->fetch_assoc()){
		$response[] = array('gname'=>$row['name'],'gid'=>$row['gid']);
	}
    $sqlResponse->close();
    $db->next_result();
	return $response;
}
/////////
function review_getPosts($gameID, $pageno, $censored) {
	global $db;
	
	$query = "select name from Games where appid='{$gameID}' limit 1;";
	$res = $db->query($query);
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	if($res->num_rows == 0){
		return false;
	}
	$row = $res->fetch_assoc();
	$gname = $row['name'];
	
	$limit = 15;
	$offset = ($pageno - 1) * $limit;
	
	$query = "select gameID from Reviews where gameID='{$gameID}'";
	$res = $db->query($query);
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	$messageCount = $res->num_rows;
	if($offset >= $messageCount)
		$offset = 0;
	
	if($messageCount == 0){
		return array('game'=>$gname,'totalMessages'=>0,'pageMessages'=>0,'messages'=>array());
	}
	
	$query = "select Users.userid, Users.username, Reviews.* from Users join Reviews on Users.userid=Reviews.userID where Reviews.gameID='{$gameID}' order by Reviews.postTime asc limit {$limit} offset {$offset};";
	
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	$pageMessages = $sqlResponse->num_rows;
	$messages = array();
	
	if($censored){
		$profs = file_get_contents('profanityList.txt', true);
		$profList = explode(",",$profs);
	}
	
	while($row = $sqlResponse->fetch_assoc()){
		$msg = $row['message'];
		if($censored)
			$msg = str_ireplace($profList, "****", $msg);
		$messages[] = array('username'=>$row['username'], 'userid'=>$row['userid'], 'postTime'=>$row['postTime'], 'message'=>$msg, 'positive'=>$row['isPositive']);
	}
	
	$response = array('game'=>$gname,'totalMessages'=>$messageCount,'pageMessages'=>$pageMessages,'messages'=>$messages);
	return $response;
}
/////////
function review_writePost($gameID, $userID, $message, $isPositive){
	global $db;
	$cleanMessage = mysqli_real_escape_string($db, $message);
	if($cleanMessage == null)
		return false;
	$query = "insert into Reviews (gameID, userID, message, isPositive) values ('{$gameID}','{$userID}','{$cleanMessage}','{$isPositive}') on duplicate key update message='{$cleanMessage}', isPositive='{$isPositive}', postTime = current_timestamp;";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	return true;
}
/////////
function forum_getPosts($gameID, $pageno, $censored) {
	global $db;
	
	$query = "select name from Games where appid='{$gameID}' limit 1;";
	$res = $db->query($query);
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	if($res->num_rows == 0){
		return false;
	}
	$row = $res->fetch_assoc();
	$gname = $row['name'];
	
	$limit = 15;
	$offset = ($pageno - 1) * $limit;
	
	$query = "select gameID from Messages where gameID='{$gameID}'";
	$res = $db->query($query);
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	$messageCount = $res->num_rows;
	if($offset >= $messageCount)
		$offset = 0;
	
	if($messageCount == 0){
		return array('game'=>$gname,'totalMessages'=>0,'pageMessages'=>0,'messages'=>array());
	}
	
	$query = "select Users.userid, Users.username, Messages.* from Users join Messages on Users.userid=Messages.userID where Messages.gameID='{$gameID}' order by Messages.postTime asc limit {$limit} offset {$offset};";
	
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	$pageMessages = $sqlResponse->num_rows;
	$messages = array();
	
	if($censored){
		$profs = file_get_contents('profanityList.txt', true);
		$profList = explode(",",$profs);
	}
	
	echo "Pre row sqlresponse while loop".PHP_EOL;
	while($row = $sqlResponse->fetch_assoc()){
		$msg = $row['message'];
		if($censored)
			$msg = str_ireplace($profList, "****", $msg);
		$messages[] = array('username'=>$row['username'], 'userid'=>$row['userid'], 'postTime'=>$row['postTime'], 'message'=>$msg);
	}
	
	echo "assemble array of response".PHP_EOL;
	$response = array('game'=>$gname,'totalMessages'=>$messageCount,'pageMessages'=>$pageMessages,'messages'=>$messages);
	echo "send response".PHP_EOL;
	return $response;
}
/////////
function forum_writePost($gameID, $userID, $message, $sendTime){
	global $db;
	$cleanMessage = mysqli_real_escape_string($db, $message);
	if($cleanMessage == null)
		return false;
	$query = "insert into Messages (gameID, userID, postTime, message) values ('{$gameID}','{$userID}',CURRENT_TIMESTAMP(),'{$cleanMessage}');";
	$sqlResponse = $db->query($query);
	
	if ($db->errno != 0)
	{
		echo "failed to execute query:".PHP_EOL;
		echo __FILE__.':'.__LINE__.":error: ".$mydb->error.PHP_EOL;
		return false;
	}
	return true;
}
////	BASICS TO RUN	////

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);
  if(!isset($request['type']))
  {
    return "ERROR: unsupported message type";
  }
  switch ($request['type'])
  {
    case "login":
      return doLogin($request['username'],$request['password'],$request['sessionId']);
    //case "create_session":
    //  return createSession($request['userId'], $request['sessionId']);
    case "validate_session":
      return validateSession($request['sessionId']);
    case "logout":
      return logout($request['sessionId']);
    case "register":
      return doRegister($request['username'],$request['password'],$request['email']);
    case "set_steam_link":
    	return steam_setlink($request['sessionId'],$request['steamId']);
    case "get_steam_profile":
    	return steam_giveUserData($request['userId']);
   	case "get_user_news":
   		return steam_getNews($request['userId']);
    //case "refresh_steamtopgames":
    //	return refresh_steamtopgames($request['games']);
    case "user_get_games":
    	return getUserGameList($request['userID'], 25);
    case "forum_get_posts":
    	return forum_getPosts($request['gameID'],$request['page'],$request['censor']);
    case "forum_add_post":
    	return forum_writePost($request['gameID'], $request['userID'], $request['message'], $request['sendTime']);
    case "review_get_posts":
    	return review_getPosts($request['gameID'],$request['page'],$request['censor']);
    case "review_add_post":
    	return review_writePost($request['gameID'], $request['userID'], $request['message'], $request['positive']);
  }
  echo "return_processor: sending request back".PHP_EOL;
  return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"databaseServer");	//For actual running.

//$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"testServer");	//For testing on localhost.

$server->process_requests('requestProcessor');
exit();
?>

