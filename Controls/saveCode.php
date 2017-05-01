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

$request = json_decode ( file_get_contents ( 'php://input' ) );
$code = new Code();
$code->setId(!empty($request->id)? $request->id : null );
$code->setCodeValue(!empty($request->code_value)? $request->code_value : null );
$code->setCodeType(!empty($request->code_type)? $request->code_type : null );
$code->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
JsonParser::sendJson($code->save());

?>