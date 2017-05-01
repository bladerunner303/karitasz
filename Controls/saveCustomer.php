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
$customer = new Customer();
$customer->setId(!empty($request->id)? $request->id : null );
$customer->setCustomerType( $request->customer_type);
$customer->setSurname($request->surname);
$customer->setForename(!empty($request->forename)? $request->forename : null );
$customer->setZip($request->zip);
$customer->setCity($request->city);
$customer->setStreet($request->street);
$customer->setDescription(!empty($request->description)? $request->description : null );
$customer->setAdditionalContact(!empty($request->additional_contact)? $request->additional_contact : null);
$customer->setAdditionalContactPhone(!empty($request->additional_contact_phone)? $request->additional_contact_phone : null);
$customer->setQualification($request->qualification);
$customer->setEmail(!empty($request->email)?$request->email:null);
$customer->setPhone($request->phone);
$customer->setPhone2(!empty($request->phone2)?$request->phone2:null);
$customer->setStatus($request->status);
$customer->setMaritalStatus(!empty($request->marital_status)?$request->marital_status:null);
$customer->setTaxNumber(!empty($request->tax_number)? $request->tax_number : null);
$customer->setTbNumber(!empty($request->tb_number)? $request->tb_number : null);
$customer->setBirthPlace(!empty($request->birth_place)? $request->birth_place : null);
$customer->setBirthDate(!empty($request->birth_date)? $request->birth_date : null);
$customer->setMotherName(!empty($request->mother_name)? $request->mother_name : null);
$customer->setModifier(Session::getUserInfo($_COOKIE['sessionId'])->userName);
$customer->setFamilyMembers($request->members);
JsonParser::sendJson($customer->save());

?>