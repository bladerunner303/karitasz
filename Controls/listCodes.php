<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$codeTypes = !empty($_GET['codeTypes']) ? explode(";", $_GET['codeTypes']) : array() ;

$ret = array();
foreach ($codeTypes as $codeType) {
	$ret[$codeType] = Code::listCode($codeType);
}
//$ret = (object)$ret;

JsonParser::sendJson($ret);

?>