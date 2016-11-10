<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$id = !empty($_GET['id']) ? $_GET['id'] : null ;
$beginDate = !empty($_GET['begin_date']) ? $_GET['begin_date'] : '2000-01-01' ;
$endDate = !empty($_GET['end_date']) ? $_GET['end_date'] : '2100-01-01' ;
$limit = !empty($_GET['limit']) ? $_GET['limit'] : null ;
$text = !empty($_GET['text']) ? $_GET['text'] : null ;

$finder = new Transport();
$finder->setId($id);

$ret = array();
$ret = $finder->find($beginDate, $endDate, $text);

if (($id != null) && (count($ret) == 1)){
	$finderAddress = new TransportAddress();
	$finderAddress->setTransportId($id);
	$ret[0]->addresses = $finderAddress->find();
}

JsonParser::sendJson($ret);

?>