<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
$operationId = !empty($_GET['id']) ? $_GET['id'] : null ;

$finder = new Transport();
$ret = new stdClass();
$ret->transports = $finder->find('1900-01-01', '2100-01-01', null, $operationId);

foreach ($ret->transports as $i => $transport) {
	$finder = new TransportAddress();
	$finder->setTransportId($transport->id);
	$transportAddresses = $finder->find();
	
	foreach ($transportAddresses as $address) {
		$ret->transports[$i]->items = $finder->findAddressItems($address->id);
	}
	
}
JsonParser::sendJson($ret);

?>