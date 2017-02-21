<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$request = json_decode ( file_get_contents ( 'php://input' ) );
$operation = new Operation();
$operation->setId(!empty($request->id)? $request->id : null );
$operation->setCustomerId( $request->customer_id);
$operation->setOperationType($request->operation_type);
$operation->setHasTransport($request->has_transport);
$operation->setIsWaitCallback($request->is_wait_callback);
$operation->setStatus($request->status);
$operation->setDescription(!empty($request->description )? $request->description : null);
$operation->setNeedinessLevel(!empty($request->neediness_level )? $request->neediness_level : null);
$operation->setSender(!empty($request->sender )? $request->sender : null);
$operation->setIncomeType(!empty($request->income_type )? $request->income_type : null);
$operation->setIncome(!empty($request->income )? $request->income : null);
$operation->setOthersIncome(!empty($request->others_income )? $request->others_income : null);
$operation->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
$operation->setOperationDetails($request->operationDetails);

JsonParser::sendJson($operation->save());

?>