<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$request = json_decode ( file_get_contents ( 'php://input' ) );

$addressId = $request->id;
$description = $request->description;
$isSetSuccessful = $request->isSetSuccessful == 'true' ;
$isSetCanceled = $request->isSetCanceled == 'true'; 
$user = Session::getUserInfo($_COOKIE['sessionId'])->userName;

if (($isSetSuccessful) || ($isSetCanceled)){
	
	$items = TransportAddress::findAddressItems($addressId);
	foreach ($items as $key => $item) {
		$item = new TransportAddress();
		$item->status = ($isSetSuccessful) ? "BEFEJEZETT_TRANSPORT" : "SIKERTELEN_TRANSPORT"; 
		TransportAddress::saveItem($item, $user);
	}
	
	$finder = new TransportAddress();
	$finder->setId($addressId);
	$addresses = $finder->find();
	$address = new TransportAddress();
	$address = SystemUtil::cast($address, $addresses[0]);
	$address->setStatus(($isSetSuccessful) ? "BEFEJEZETT_TRANSPORT" : "SIKERTELEN_TRANSPORT");
	$address->save();
	
	JsonParser::sendJson('OK');
}
else {
	TransportAddress::addDescription($addressId, $description, $user);
	JsonParser::sendJson('OK');
}

?>