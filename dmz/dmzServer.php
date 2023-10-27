#!/usr/bin/php
<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');

function callAPI($url) 
{

	// Create a new cURL resource
	$curl = curl_init();

	if (!$curl) {
		die("Couldn't initialize a cURL handle");
	}

	// Set the file URL to fetch through cURL
	curl_setopt($curl, CURLOPT_URL, $url);

	// Fail the cURL request if response code = 400 (like 404 errors)
	curl_setopt($curl, CURLOPT_FAILONERROR, true);

	// Returns the status code
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

	//Supresses output?
	curl_setopt($curl, CURLOPT_HEADER, false);

	// Wait 10 seconds to connect and set 0 to wait indefinitely
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

	// Execute the cURL request for a maximum of 50 seconds
	curl_setopt($curl, CURLOPT_TIMEOUT, 50);

	// Do not check the SSL certificates
	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

	// Fetch the URL and save the content in $html variable
	$output = curl_exec($curl);

	// Check if any error has occurred
	if (curl_errno($curl))
	{
		echo 'cURL error: ' . curl_error($curl);
	}
	else
	{
		// cURL executed successfully
		//print_r(curl_getinfo($curl));
		// close cURL resource to free up system resources
		curl_close($curl);
		// will display the page contents i.e its html.
		return $output;
	}
}


function check_steam_id($id)
{
  $steamid = 76561198118290580; //get the steamid from wherever else it's needed
  $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=6F640B29184C9FE8394A82EEAEFC9A8B&steamids=$id";

  $profileData= callAPI($url);

  $profileDecode = json_decode($profileData);
  //var_dump($profileDecode);
  foreach($profileDecode as $response=>$obj1)
  {
    foreach($obj1 as $players=>$obj2)
    {
        foreach($obj2 as $hidden=>$obj3)
        {
            foreach($obj3 as $param=>$passedVal)
            {
                if($param == 'communityvisibilitystate' && $passedVal == 3)
                {
                  echo "Given STEAM id valid, returning true".PHP_EOL;
                  return true;
                }
                //echo $passedVal."\n";
            }
        }
    }
  }
  echo "Given STEAM id is invalid or inaccessible, user error".PHP_EOL;
	return false;
}

function get_steam_profile($id)
{
  $steamid = 76561198118290580; //get the steamid from wherever else it's needed
  $url = "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v2/?key=6F640B29184C9FE8394A82EEAEFC9A8B&steamids=$id";

  $profileData= callAPI($url);

  $profileDecode = json_decode($profileData);
  //var_dump($profileDecode);

  $profileArray = array('username'=>NULL, 'avatar'=>NULL);

  foreach($profileDecode as $response=>$obj1)
  {
    foreach($obj1 as $players=>$obj2)
    {
        foreach($obj2 as $hidden=>$obj3)
        {
            foreach($obj3 as $param=>$passedVal)
            {
                if($param == 'personaname' && $passedVal != NULL)
                {
                  echo "Given username is valid, returning true".PHP_EOL;
                  $profileArray['username'] = $passedVal;
                }
                if($param == 'avatarmedium' && $passedVal != NULL)
                {
                  echo "Given avatar url is valid, returning true".PHP_EOL;  
                  $profileArray['avatar'] = $passedVal;
                }
            }
        }
    }
  }

if(is_null($profileArray['username']) || is_null($profileArray['avatar']) ){
  echo "Missing username or avatar data".PHP_EOL;
  return false;
}
else{
  echo "Username and Avatar valid".PHP_EOL;
  return $profileArray;
}

}


function get_user_library($id)
{
  $url = "https://api.steampowered.com/IPlayerService/GetOwnedGames/v1/?key=6F640B29184C9FE8394A82EEAEFC9A8B&include_appinfo=true&include_played_free_games=true&steamid=$id";

  $libraryData= callAPI($url);

  $libraryDecode = json_decode($libraryData);
    //var_dump($libraryDecode);
  $libraryArray = array();
  foreach($libraryDecode as $response=>$obj1)
  {
      foreach($obj1 as $games=>$obj2)
      {
          if($games == 'games')
          {
              foreach($obj2 as $gameIndex=>$obj3)
              {
                  $libraryArray[$gameIndex] = array(
                      'appid'=>NULL,
                      'name'=>NULL,
                      'playtime'=>NULL
                  );
                  foreach($obj3 as $keyName=>$passedVal)
                  {
                      if($keyName == 'appid' && $passedVal != NULL)
                      {
                          //echo "AppId valid".PHP_EOL;
                          $libraryArray[$gameIndex]['appid'] = $passedVal;
                      }
                      if($keyName == 'name' && $passedVal != NULL)
                      {
                          //echo "Name Exists".PHP_EOL;
                          $libraryArray[$gameIndex]['name'] = $passedVal;
                      }
                      if($keyName == 'playtime_forever' && $passedVal != NULL)
                      {
                          //echo "This game has been played".PHP_EOL;
                          $libraryArray[$gameIndex]['playtime'] = $passedVal;
                      }
                  }
              }
          }
      }
  }

  if(empty($libraryArray))
  {
    echo "Library Array Empty".PHP_EOL;
    return false;
  }
  echo "Given library was populated, returning array".PHP_EOL;
	return $libraryArray;
}

function get_app_info($id)
{
  $url = "https://steamspy.com/api.php?request=appdetails&appid=$id";

  $appInfo= callAPI($url);

  $appDecode = json_decode($appInfo);
  //var_dump($appDecode);
  $appArray = array();


  foreach($appDecode as $key=>$value)
  {
      //insert data here
      
  }
}

function get_app_news($appId)
{
  $url = "https://api.steampowered.com/ISteamNews/GetNewsForApp/v2/?appid=$appId&count=8&feeds=steam_community_announcements,PCGamesN";

  $newsData= callAPI($url);

  $newsDecode = json_decode($newsData);
  //var_dump($newsDecode);
  $newsArray = array();
  foreach($newsDecode as $appnews=>$obj1)
  {
      foreach($obj1 as $newsitems=>$obj2)
      {
          if($newsitems == 'newsitems')
          {
              foreach($obj2 as $newsIndex=>$obj3)
              {
                  $newsArray[$newsIndex] = array(
                      'title'=>NULL,
                      'link'=>NULL,
                      'author'=>NULL
                  );
                  foreach($obj3 as $newsKey=>$newsValue)
                  {
                      if($newsKey == 'title' && $newsValue != NULL){
                          $newsArray[$newsIndex]['title'] = $newsValue;
                      }
                      if($newsKey == 'url' && $newsValue != NULL){
                          $newsArray[$newsIndex]['link'] = $newsValue;
                      }
                      if($newsKey == 'feedlabel' && $newsValue != NULL){
                          $newsArray[$newsIndex]['author'] = $newsValue;
                      }
                  }
              }
          }
      }
  }
  //var_dump($newsArray);

  if(empty($newsArray))
    {
      echo "News Array Empty".PHP_EOL;
      return false;
    }
  echo "Given news data was populated, returning array".PHP_EOL;
  return $newsArray;
}

function getTop100()
{
  $gameData = callAPI($url);
  $dataDecode = json_decode($gameData, true);

  $gamesArray = array();
  $increment = 0;
  foreach ($dataDecode as $dataName=>$dataValue)
  {
      $gamesArray[$increment] = array(
          'appid'=>NULL,
          'name'=>NULL,
          'developer'=>NULL,
          'price'=>NULL
      );
      $increment++;
      foreach($dataValue as $key=>$value)
      {
          if($key == 'appid')
          {
              $gamesArray[$increment]['appid'] = $value;
              //array_push($steamid, $value);
          }
          if($key == 'name')
          {
              $gamesArray[$increment]['name'] = $value;
              //array_push($name, $value);
          }
          if($key == 'developer')
          {
              $gamesArray[$increment]['developer'] = $value;
              //array_push($developer, $value);
          }
          if($key == 'price')
          {
              $gamesArray[$increment]['price'] = $value;
              //array_push($price, $value);
          }
      }
  }

  if(empty($gamesArray))
    {
      echo "Game Array Empty".PHP_EOL;
      return false;
    }
  echo "Given games data was populated, returning array".PHP_EOL;
  return $gamesArray;
}

function requestProcessor($request)
{
  echo "received request".PHP_EOL;
  var_dump($request);

  if(!isset($request['type']))
  {
    return "ERROR: Unsupported or Unknown message type";
  }

  switch ($request['type'])
  {
	  case "check_steam_id":
	    return check_steam_id($request['id']);
    case "get_steam_profile":
      return get_steam_profile($request['id']);
    case "get_user_library":
      return get_user_library($request['id']);
    case "get_app_info":
      return get_app_info($request['id']);
    case "get_app_news":
      return get_app_news($request['id']);
    //case "get_game_news":
      //return get_game_news:($request[])
      //return a collection of news information based on passed in array of idays


    //case "login":
      //return doLogin($request['username'],$request['password'],$request['sessionId']);
    //case "validate_session":
      //return validateSession($request['sessionId']);
    //case "register":
      //return doRegister($request['username'],$request['password'],$request['email']);
  }
  return array("returnCode" => '0', 'message'=>"DMZ Server received request and processed");
}

$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"dmzServer");	//For actual running.

$server->process_requests('requestProcessor');
exit();
?>