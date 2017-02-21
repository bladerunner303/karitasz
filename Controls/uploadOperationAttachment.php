<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$operationId = (empty($_GET['operation_id']))? null : $_GET['operation_id'];
$fileName = $_FILES['userfile']['name'];
$fileType = $_FILES['userfile']['type']; //The mime type of the file, if the browser provided this information. An example would be "image/gif".
$fileSize = (int)$_FILES['userfile']['size']; //The size, in bytes, of the uploaded file.
$tempFileName = $_FILES['userfile']['tmp_name']; //The temporary filename of the file in which the uploaded file was stored on the server.
$errorCode = $_FILES['userfile']['error']; //The error code associated with this file upload. ['error'] was added in PHP 4.2.0

if (!empty($fileName)){
	
	$enabledFileSize = (int)Config::getContextParam("OPERATION_ATTACHMENT_MAX_SIZE_IN_BYTE");
	if (($enabledFileSize != 0) && ($enabledFileSize < $fileSize)){
		JsonParser::sendError(500, "A fájl mérete nagyobb mint az engedélyezett!");
		return;
	}
	
	$buffer = file_get_contents($tempFileName);
	$length = filesize($tempFileName);
	if (!$buffer || !$length) {
		die("Reading error\n");
	}
	
	$file = new File();
	$file->setCreator(Session::getUserInfo($_COOKIE['sessionId'])->userName);
	$file->setName($fileName);
	$file->setExtension($fileType);
	$file->setSize($fileSize);
	$file->setContent($buffer);
	
	
	if (empty($operationId)){
		//TODO: Formátum ellenőrzés
		JsonParser::sendJson($file->save());
	}
	else {
		$operation = new Operation();
		$operation->setId($operationId);	
		$operation->addOperationAttachment($file);
		JsonParser::sendJson("OK");
	}
}

?>