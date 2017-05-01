<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$id = !empty($_GET['id']) ? $_GET['id'] : null ;
$beginDate = !empty($_GET['begin_date']) ? $_GET['begin_date'] : '2000-01-01' ;
$endDate = !empty($_GET['end_date']) ? $_GET['end_date'] : '2100-01-01' ;
$limit = !empty($_GET['limit']) ? $_GET['limit'] : null ;
$text = !empty($_GET['text']) ? $_GET['text'] : null ;

$finder = new Transport();
$finder->setId($id);

$ret = array();
$ret = $finder->find($beginDate, $endDate, $text);

if (count($ret) == 1){
	$finderAddress = new TransportAddress();
	$finderAddress->setTransportId($ret[0]->id);
	$ret[0]->addresses = $finderAddress->find();
	
	if ($finderAddress->getStatus() != 'ROGZITETT_TRANSPORT'){
		foreach ($ret[0]->addresses as $index => $address) {
			$ret[0]->addresses[$index]->items = TransportAddress::findAddressItems($address->id);
		}
	}
}

JsonParser::sendJson($ret);

?>