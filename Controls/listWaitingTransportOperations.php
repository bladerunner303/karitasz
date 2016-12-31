<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$text = !empty($_GET['text']) ? $_GET['text'] : null ;
$dirtyReservedIds = !empty($_GET['reservedIds']) ? explode(';', $_GET['reservedIds']) : array() ;
$reservedIds = array();
foreach ($dirtyReservedIds as $dirtyId) {
	if (is_numeric($dirtyId)){
		array_push($reservedIds, $dirtyId);
	}
}

JsonParser::sendJson((new Operation())->findWaiting($text, $reservedIds));
?>