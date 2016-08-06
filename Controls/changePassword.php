<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$request = json_decode ( file_get_contents ( 'php://input' ) );
$oldPassword = $request->oldPassword;
$newPassword = $request->newPassword;
$currentUser = Session::getUserInfo($_COOKIE['sessionId'])->userName;

User::changePassword($oldPassword, $newPassword,$currentUser );
JsonParser::sendJson("OK");

?>