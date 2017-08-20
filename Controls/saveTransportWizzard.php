<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

Logger::warning(file_get_contents ( 'php://input' ));
$request = json_decode ( file_get_contents ( 'php://input' ) );
$requestOperation = $request->operation;
$requestCustomer = $request->customer;

if (empty($requestCustomer->id)){
	//New customer
	$customer = new Customer();
	$customer->setId ( null );
	$customer->setCustomerType( $requestCustomer->customer_type);
	$customer->setSurname($requestCustomer->surname);
	$customer->setForename(!empty($requestCustomer->forename)? $requestCustomer->forename : null );
	$customer->setZip($requestCustomer->zip);
	$customer->setCity($requestCustomer->city);
	$customer->setStreet($requestCustomer->street);
	$customer->setDescription(!empty($requestCustomer->description)? $requestCustomer->description : null );
	$customer->setEmail(!empty($requestCustomer->email)?$requestCustomer->email:null);
	$customer->setPhone($requestCustomer->phone);
	$customer->setStatus("AKTIV");
	$customer->setQualification('NORMAL');
	$customer->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
	$customer->setFamilyMembers(array());
	$customer->setId($customer->save());
	$requestCustomer->id = $customer->getId();
}
else {
	//Check found
	$customer = new Customer();
	$customer->setId ( $requestCustomer->id );
	
	$findCustomers = $customer->find(null, 1);
	if (count($findCustomers) != 1){
		throw new InvalidArgumentException("Nem található az ügyfél!");
	}
	else {
		$customer = SystemUtil::cast($customer, $findCustomers[0]);
		
		if (($customer->getZip() != $requestCustomer->zip) ||
			($customer->getCity() != $requestCustomer->city) ||
			($customer->getStreet() != $requestCustomer->street)){
				$customer->setZip($requestOperation->zip);
				$customer->setCity($requestOperation->city);
				$customer->setStreet($requestOperation->street);
				$customer->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
				$customer->id = $customer->save();	
			}
		
	}
	
}

$operation = new Operation();
$operation->setId( null );
$operation->setCustomerId( $requestCustomer->id);
$operation->setOperationType(($requestCustomer->customer_type == "KERVENYEZO")? "KERVENYEZES":"FELAJANLAS" );
$operation->setHasTransport(TRUE);
$operation->setIsWaitCallback(FALSE);
$operation->setStatus("AKTIV");
//$operation->setDescription($requestCustomer->description);
$operation->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
$operation->setOperationDetails($requestOperation->elements);

JsonParser::sendJson($operation->save());

?>