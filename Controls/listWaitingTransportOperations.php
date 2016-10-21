<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$text = !empty($_GET['text']) ? $_GET['text'] : null ;
JsonParser::sendJson((new Operation())->findWaiting($text));
?>