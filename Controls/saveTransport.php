<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$request = json_decode ( file_get_contents ( 'php://input' ) );
$transport = new Transport();
$transport->setId(!empty($request->id)? $request->id : null );
$transport->setTransportDate( $request->transport_date);
$transport->setStatus($request->status);
$transport->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
$transport->setAddresses($request->addresses);

JsonParser::sendJson($transport->save());

?>