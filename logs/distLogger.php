<?php
require_once(__DIR__.'/../src/include/path.inc');
require_once(__DIR__.'/../src/include/get_host_info.inc');
require_once(__DIR__.'/../src/include/rabbitMQLib.inc');
require_once(__DIR__.'/../src/include/loginbase.inc');

$server = new rabbitMQServer(__DIR__.'/../src/include/testRabbitMQ.ini',"logServer"); //For actual running.

function requestProcessor($request)
{
echo "received message".PHP_EOL;
var_dump($request);
return array("returnCode" => '0', 'message'=>"Server received request and processed");
}

$server->process_requests('requestProcessor');

?>