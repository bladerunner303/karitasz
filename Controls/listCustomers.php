<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$id = !empty($_GET['id']) ? $_GET['id'] : null ;
$customerType = !empty($_GET['customer_type']) ? $_GET['customer_type'] : null ;
$text = !empty($_GET['text'])?$_GET['text']:null;
$limit = !empty($_GET['limit']) ? $_GET['limit'] : null ;

$finder = new Customer();
$finder->setId($id);
$finder->setCustomerType($customerType);

JsonParser::sendJson($finder->find($text, $limit));

?>