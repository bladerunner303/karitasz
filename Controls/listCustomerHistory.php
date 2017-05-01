<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
if (!SessionUtil::validRole(array("ROLE_BACK_OFFICE"))){
	JsonParser::sendRoleError();
	return;
}

SessionUtil::logControlRun(basename(__FILE__));

$id = !empty($_GET['id']) ? $_GET['id'] : null ;

$finder = new Customer();
$finder->setId($id);

JsonParser::sendJson($finder->listHistory());

?>