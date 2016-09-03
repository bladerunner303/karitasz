<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$id = !empty($_GET['id']) ? $_GET['id'] : null ;
$customerId = !empty($_GET['customer_id']) ? $_GET['customer_id'] : null ;
$text = !empty($_GET['text']) ? $_GET['text'] : null ;
$status = !empty($_GET['status']) ? $_GET['status'] : null ;
$isWaitCallback = !empty($_GET['wait_callback'])?$_GET['wait_callback']:null;
$operationType = !empty($_GET['operation_type'])?$_GET['operation_type']:null;
$limit = !empty($_GET['limit']) ? $_GET['limit'] : null ;
$detail = !empty($_GET['detail']) ? $_GET['detail'] : null;

$finder = new Operation();
$finder->setId($id);
$finder->setCustomerId($customerId);
$finder->setStatus($status);
$finder->setIsWaitCallback($isWaitCallback);
$finder->setOperationType($operationType);

$ret = array();
if (empty($detail)){
	$ret = $finder->find($text, $limit);
}
else {
	$ret = $finder->findByDetails($detail);
}

if (($id != null) && (count($ret) == 1)){
	$finderDetail = new OperationDetail();
	$finderDetail->setOperationId($id);
	$ret[0]->operationDetails = $finderDetail->find();
}

JsonParser::sendJson($ret);

?>