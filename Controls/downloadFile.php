<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$id = $_GET['file_id'];
$finder = new File();
$finder->setId($id);
$files = $finder->find();
if (count($files) != 1) {
	header("Content-Disposition: attachment; filename=error.txt");
	echo "Nem található a fájl!";
}
else {
	$file = new File();
	$file = SystemUtil::cast($file, $files[0]);
	header("Content-length: " . $file->getSize());
	header("Content-type: " . $file->getExtension());
	header("Content-Disposition: attachment; filename=".$file->getName());
	echo $file->getContent();
}

?>