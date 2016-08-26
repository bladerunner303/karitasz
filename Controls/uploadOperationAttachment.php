<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$operationId = $_GET['operation_id'];
$fileName = $_FILES['userfile']['name'];
$fileType = $_FILES['userfile']['type']; //The mime type of the file, if the browser provided this information. An example would be "image/gif".
$fileSize = $_FILES['userfile']['size']; //The size, in bytes, of the uploaded file.
$tempFileName = $_FILES['userfile']['tmp_name']; //The temporary filename of the file in which the uploaded file was stored on the server.
$errorCode = $_FILES['userfile']['error']; //The error code associated with this file upload. ['error'] was added in PHP 4.2.0

if (!empty($fileName)){
	
	$buffer = file_get_contents($tempFileName);
	$length = filesize($tempFileName);
	if (!$buffer || !$length) {
		die("Reading error\n");
	}
	
	$operation = new Operation();
	$operation->setId($operationId);
	$file = new File();
	$file->setCreator(Session::getUserInfo($_COOKIE['sessionId'])->userName);
	$file->setName($fileName);
	$file->setExtension($fileType);
	$file->setSize($fileSize);
	$file->setContent($buffer);
	
	$operation->addOperationAttachment($file);
	JsonParser::sendJson("OK");
}

?>