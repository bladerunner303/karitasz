<?php

require_once '../Util/Loader.php';
ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$request = json_decode ( file_get_contents ( 'php://input' ) );
$requestOperation = $request->operation;
$requestCustomer = $request->customer;
$requestTransportId = $request->transportId;

$findTransport = new Transport();
$findTransport->setId($requestTransportId);
$transports = $findTransport->find('1900-01-01', '2100-01-01', null, null);
if (count($transports) != 1) {
	throw new InvalidArgumentException("Nem található az szállítás!");
}

//Ügyfél mentés
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
				$customer->setZip($requestCustomer->zip);
				$customer->setCity($requestCustomer->city);
				$customer->setStreet($requestCustomer->street);
				$customer->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
				$customer->id = $customer->save();	
			}
		
	}
	
}

//Művelet létrehozás
$operation = new Operation();
$operation->setId( null );
$operation->setCustomerId( $requestCustomer->id);
$operation->setOperationType(($requestCustomer->customer_type == "KERVENYEZO")? "KERVENYEZES":"FELAJANLAS" );
$operation->setHasTransport(TRUE);
$operation->setIsWaitCallback(FALSE);
$operation->setStatus("FOLYAMATBAN");
//$operation->setDescription($requestCustomer->description);
$operation->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
$operation->setOperationDetails($requestOperation->elements);

$operation->setId($operation->save());

/*
//Szállításba cím létrehozás
$address = new stdClass();
$address->operation_id = $operation->getId();
$address->transport_id = $requestTransportId;
$address->status  = "KIADOTT_TRANSPORT";
$address->zip = $requestCustomer->zip;
$address->city = $requestCustomer->city;
$address->street = $requestCustomer->street;
$address->phone = $requestCustomer->phone;
$address->description = null;
$address->order_indicator = count($transport->addresses);


array_push($transport->addresses, $address);
$typedTransport = new Transport();
SystemUtil::cast($typedTransport, $transport);
$typedTransport->save();
*/


$transport = $transports[0];
$address = new TransportAddress();
$address->setId(null );
$address->setTransportId($requestTransportId);
$address->setOperationId($operation->getId());
$address->setZip($requestCustomer->zip);
$address->setCity($requestCustomer->city);
$address->setStreet($requestCustomer->street);
$address->setDescription(null);
$address->setStatus("KIADOTT_TRANSPORT");
$address->setOrderIndicator(count($transport->addresses));
$address->setId($address->save());
	
//Szállítási címhez elem létrehozás
TransportAddress::generateAddressItems($address->getId(), $operation->getId(), Session::getUserInfo($_COOKIE['sessionId'])->userName);

$ret = new stdClass();
$ret->operation = $operation;
$ret->address = $address; 
$ret->address->items = TransportAddress::findAddressItems($address->getId());
$ret->customer = $customer;

JsonParser::sendJson($ret);

?>