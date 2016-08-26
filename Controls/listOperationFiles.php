<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$id = !empty($_GET['id']) ? $_GET['id'] : null ;
$finder = new Operation();
$finder->setId($id);
JsonParser::sendJson($finder->listOperationFiles());

?>