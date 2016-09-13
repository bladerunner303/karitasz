<?php

require_once '../Util/Loader.php';

 ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}

$id = !empty($_GET['id']) ?  $_GET['id'] : null ;
$surname  = !empty($_GET['surname']) ? $_GET['surname'] : null ;
$forename = !empty($_GET['forename']) ?  $_GET['forename'] : null ;
$zip = !empty($_GET['zip']) ?  $_GET['zip'] : null ;
$street = !empty($_GET['street']) ?  $_GET['street'] : null ;
$phone = !empty($_GET['phone']) ?  $_GET['phone'] : null ;
$phone2 = !empty($_GET['phone2']) ?  $_GET['phone2'] : null ;
$taxNumber = !empty($_GET['tax_number']) ? $_GET['tax_number']: null;
$tbNumber = !empty($_GET['tb_number']) ? $_GET['tb_number']: null;

$finder = new Customer();
$finder->setId($id);
$finder->setSurname($surname);
$finder->setForename($forename);
$finder->setZip($zip);
$finder->setStreet($street);
$finder->setPhone($phone);
$finder->setPhone2($phone2);
$finder->setTaxNumber($taxNumber);
$finder->setTbNumber($tbNumber);

JsonParser::sendJson($finder->findSimilar());

?>