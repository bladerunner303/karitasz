<?php
//session_start();
require_once '../Util/Loader.php';

ErrorHandler::register();


if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
$fileName = $_POST['fileName'];
$columnList = $_POST['columnList'];
$contentArray = json_decode($_POST['contentArray']);

$content = $columnList . "\r\n"; 
foreach ($contentArray as $row) {
	$content .= implode(";", $row) . ";" . "\r\n";
}

header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=" . $fileName . ".csv");
header("Pragma: no-cache");
header("Expires: 0");

echo $content;

?>