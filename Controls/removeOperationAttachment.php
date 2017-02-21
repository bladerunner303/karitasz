<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$request = json_decode ( file_get_contents ( 'php://input' ) );
$file = new File();
$file->setId(!empty($request->id)? $request->id : null );
$file->remove();
JsonParser::sendJson("OK");

?>