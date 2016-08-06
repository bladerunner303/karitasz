<?php

require_once '../Util/Loader.php';

class Customer implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}

	/**
	 * @return array<Customer>
	 */
	public function findSimilar(){
		
		$sql = "select distinct
					x.id,
					x.full_name,
					x.full_address,
					x.phone,
					x.qualification,
					x.tax_number,
					x.tb_number,
					code.code_value qualification_local 
				from (
					select 
						c0.id,
						trim(concat(c0.surname, ' ', coalesce(c0.forename, ''))) full_name,
						concat(c0.zip, ' ', c0.city, ' ' , c0.street) full_address,
						c0.phone phone,
						c0.qualification qualification,
						c0.tax_number,
						c0.tb_number,
						'Adó vagy taj szám egyezés' similar_reason ,
						5 order_num
						
					from 
						customer c0
					where  (c0.tax_number = :tax_number and c0.tax_number is not null) 
				    or (c0.tb_number = :tb_number and c0.tb_number is not null)
				
				    union
					select 
						c.id,
						trim(concat(c.surname, ' ', coalesce(c.forename, ''))) full_name,
						concat(c.zip, ' ', c.city, ' ' , c.street) full_address,
						c.phone phone,
						c.qualification qualification,
						c.tax_number,
						c.tb_number,
						'Telefonszám egyezés' similar_reason ,
						10 order_num
						
					from 
						customer c
					where  c.phone = :phone
					
					union 
					select 
						c2.id,
						trim(concat(c2.surname, ' ', coalesce(c2.forename, ''))) full_name,
						concat(c2.zip, ' ', c2.city, ' ' , c2.street) full_address,
						c2.phone phone,
						c2.qualification qualification,
						c2.tax_number,
						c2.tb_number,
						'Név egyezés' similar_reason ,
						20 order_num
					from customer c2
					where (c2.surname like concat('%', :surname, '%') and c2.forename like concat('%', coalesce(:forename, ''), '%'))
					or c2.surname like concat('%', :surname, '%')
						
					union
					select
						c3.id,
						trim(concat(c3.surname, ' ', coalesce(c3.forename, ''))) full_name,
						concat(c3.zip, ' ', c3.city, ' ' , c3.street) full_address,
						c3.phone phone,
						c3.qualification qualification,
						c3.tax_number,
						c3.tb_number,
						'Cím egyezés' similar_reason,
						30 order_num
					from customer c3
					where  c3.zip = :zip and c3.street like concat('%', :street,'%')
				) as x,
				code 
				where x.qualification = code.id 
				and x.id != coalesce(:id, '')
				order by x.order_num, x.full_name
				limit 20";
		
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':surname' => $this->surname,
			    ':forename' => $this->forename,
				':zip' => $this->zip,
				':street' => $this->street,
				':phone' => $this->phone,
				':tax_number' => $this->tax_number,
				':tb_number' => $this->tb_number
		);
		
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);			
				 
		
	}
	
	
	/**
	 * @return array<Customer>
	 */
	public function find($text, $limit){
		
		if (($limit == null) || ($limit < 1)) {
			$limit = 100000; //Primitiv de működő megoldás
		}
		
		$sql = "select 
					c.*, 
					trim(concat(c.surname, ' ', coalesce(c.forename, ''))) full_name,
					concat(c.zip, ' ', c.city, ' ' , c.street) full_address,
					code_status.code_value status_local,
					code_qualification.code_value qualificaton_local,
					code_type.code_value customer_type_local,
					concat(c.created, ' (', c.creator, ')') created_info,
					concat(c.modified, ' (', c.modifier, ')') modified_info
				from 
					customer c,
					code code_status,
					code code_qualification,
					code code_type
				where 1=1
				and c.status = code_status.id
				and c.qualification = code_qualification.id
				and c.customer_type = code_type.id
				and (:id is null or c.id = :id)
				and (:customer_type is null or c.customer_type = :customer_type)
				and concat( c.id, 
							c.surname, 
							coalesce(c.forename, ''),
							c.zip,
						    c.city, 
						    c.street, 
						    c.phone, 
						    coalesce(c.description, ''),
						    coalesce(c.additional_contact, ''),
						    coalesce(c.additional_contact_phone, ''),
						    c.qualification,
						   	code_status.code_value ,
							code_qualification.code_value ,
							code_type.code_value ,
							coalesce(c.tax_number, ''),
							coalesce(c.tb_number, ''),
							coalesce(c.birth_place, '')
						     )
					like concat('%', coalesce(:text, ''), '%')
				order by c.surname, c.forename
				limit :limit";
		
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':customer_type', $this->customer_type, PDO::PARAM_STR);
		$pre->bindValue(':text', $text, PDO::PARAM_STR);
		$pre->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
		
		
		$pre->execute();
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	/**
	 * @return array<CustomerHistory>
	 */
	public function listHistory(){
		$sql = "select 
					ch.* ,
					concat(ch.created, ' (', ch.creator, ')') created_info,
					c.code_value data_type_local
				from 
					customer_history ch, 
					code c 
				where c.id = ch.data_type 
				and ch.customer_id = :id
				order by ch.created desc";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id
		);
		
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	/**
	 * @return string
	 */
	public function save(){

		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
		
		if (empty($this->id)){
		
			$seqNext = $db->query("select sequence_nextval() nextval")->fetch(PDO::FETCH_OBJ)->nextval;
			$this->id = ($this->customer_type == 'KERVENYEZO' ? 'K': 'F') . str_pad($seqNext,6,"0",STR_PAD_LEFT);
			
			$pre = $db->prepare("insert into customer 
								( id, surname, forename, customer_type, zip, city, street, phone, qualification, description, 
								  additional_contact, additional_contact_phone, status, tax_number, tb_number, birth_place, birth_date, 
								creator, modifier, created, modified) 
						 values (
								  :id, :surname, :forename, :customer_type, :zip, :city, :street, :phone, :qualification, :description, 
								  :additional_contact, :additional_contact_phone, :status, :tax_number, :tb_number, :birth_place, :birth_date, 
									:creator, :modifier, :created, :modified 
								)");
			$params = array(
					':id' => $this->id,
					':surname' => $this->surname,
					':forename' => $this->forename,
					':customer_type' => $this->customer_type,
					':zip'=>$this->zip,
					':city'=>$this->city,
					':street'=>$this->street,
					':phone'=>$this->phone,
					':qualification' => $this->qualification,
					':description' => $this->description,
					':additional_contact' => $this->additional_contact,
					':additional_contact_phone' => $this->additional_contact_phone,
					':status' => $this->status,
					':tax_number' => $this->tax_number,
					':tb_number' => $this->tb_number,
					':birth_place' => $this->birth_place,
					':birth_date' => $this->birth_date,
					':creator'=>$this->modifier,
					':modifier'=>$this->modifier,
					':created'=> $t,
					':modified'=> $t
			);

			$pre->execute($params);
		}
		else {
			
			$findCustomer = new Customer();		
			$findCustomer->setId( $this->id );
			$originalList = $findCustomer->find(null, 1);
			$original = new Customer();
			SystemUtil::cast($original, $originalList[0]);
			
			if (
						($original->getSurname()!= $this->surname)
					||  ($original->getForename() != $this->forename)
					||	($original->getZip() != $this->zip)
					||	($original->getCity() != $this->city)
					||	($original->getStreet() != $this->street)
					||	($original->getPhone() != $this->phone)
					||  ($original->getQualification() != $this->qualification)
					||	($original->getDescription() != $this->description)
					||	($original->getAdditionalContact() != $this->additional_contact)
					||	($original->getAdditionalContactPhone() != $this->additional_contact_phone)
					||	($original->getStatus() != $this->status)
					||	($original->getTaxNumber() != $this->tax_number)
					||	($original->getTbNumber() != $this->tb_number)
					||	($original->getBirthPlace() != $this->birth_place)
					||	($original->getBirthDate() != $this->birth_date)
					
					){
				
				$db->beginTransaction();
				try {
					$pre = $db->prepare("update customer
							set
							surname = :surname,
							forename = :forename,
							zip = :zip,
							city = :city,
							street = :street,
							phone = :phone,
							qualification = :qualification,
							description = :description,
							additional_contact = :additional_contact,
							additional_contact_phone = :additional_contact_phone,
							status = :status,
							tax_number = :tax_number,
							tb_number = :tb_number,
							birth_place = :birth_place,
							birth_date = :birth_date,
							modifier = :modifier,
							modified = :modified
							where
							id = :id
							");
					
					$params = array(
							':surname' => $this->surname,
							':forename' => $this->forename,
							':zip'=>$this->zip,
							':city'=>$this->city,
							':street'=>$this->street,
							':phone'=>$this->phone,
							':qualification' => $this->qualification,
							':description' => $this->description,
							':additional_contact' => $this->additional_contact,
							':additional_contact_phone' => $this->additional_contact_phone,
							':status' => $this->status,
							':tax_number' => $this->tax_number,
							':tb_number' => $this->tb_number,
							':birth_place' => $this->birth_place,
							':birth_date' => $this->birth_date,
							':modifier'=>$this->modifier,
							':modified'=>$t,
							':id'=>$this->id
					);
					
					$pre->execute($params);
					
					$pre = $db->prepare("insert into customer_history
							(id, customer_id, data_type, old_value, new_value, creator, created)
							values
							(:id, :customer_id, :data_type, :old_value, :new_value, :creator, :created) ");
					
					if (($original->getSurname()!= $this->surname)
							|| ($original->getForename() != $this->forename)){
							
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'NAME_CHANGE',
								':old_value'=>$original->getSurname() . ' ' . $original->getForename(),
								':new_value'=>$this->getSurname() . ' ' . $this->getForename(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
							
					}
					
					if (($original->getZip() != $this->zip)
							||	($original->getCity() != $this->city)
							||	($original->getStreet() != $this->street)) {
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'ADDRESS_CHANGE',
								':old_value'=>$original->getZip() . ' ' . $original->getCity() . ' ' . $original->getStreet(),
								':new_value'=>$this->getZip() . ' ' . $this->getCity() . ' ' . $this->getStreet(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					if ($original->getPhone() != $this->phone){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'PHONE_CHANGE',
								':old_value'=>$original->getPhone() ,
								':new_value'=>$this->getPhone(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					if ($original->getQualification() != $this->qualification){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'QUALIFICATION_CHANGE',
								':old_value'=>$original->getQualification() ,
								':new_value'=>$this->getQualification(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if	($original->getDescription() != $this->description){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'DESCRIPTION_CHANGE',
								':old_value'=>$original->getDescription() ,
								':new_value'=>$this->getDescription(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if (($original->getAdditionalContact() != $this->additional_contact)
							|| ($original->getAdditionalContactPhone() != $this->additional_contact_phone)){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'ADD_CONTACT_CHANGE',
								':old_value'=>$original->getAdditionalContact() . ' ' .  $original->getAdditionalContactPhone() ,
								':new_value'=>$this->getAdditionalContact() . ' ' . $this->getAdditionalContactPhone(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if ($original->getStatus() != $this->status){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'STATUS_CHANGE',
								':old_value'=>$original->getStatus() ,
								':new_value'=>$this->getStatus(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if ($original->getTaxNumber() != $this->tax_number){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'TAX_NUMBER_CHANGE',
								':old_value'=>$original->getTaxNumber() ,
								':new_value'=>$this->getTaxNumber(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if ($original->getTbNumber() != $this->tb_number){
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'TB_NUMBER_CHANGE',
								':old_value'=>$original->getTbNumber() ,
								':new_value'=>$this->getTbNumber(),
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					if (($original->getBirthPlace() != $this->birth_place)
						||($original->getBirthDate() != $this->birth_date)) 
					{
						$params = array(
								':id' => SystemUtil::getGuid(),
								':customer_id' => $this->id,
								':data_type'=>'BIRTH_DATA_CHANGE',
								':old_value'=>$original->getBirthPlace() . ' ' . $original->getBirthDate() ,
								':new_value'=>$this->getBirthPlace() . ' ' . $this->getBirthDate() ,
								':creator'=>$this->modifier,
								':created'=>$t
						);
						$pre->execute($params);
					}
					
					$db->commit();
				} catch (Exception $e) {
					$db->rollback();
					throw $e;
				}
				
			}
		}
		return $this->id;

	}

	private $id;
	private $customer_type;
	private $surname;
	private $forename;
	private $zip ;
	private $city;
	private $street;
	private $phone;
	private $qualification;
	private $description;
	private $additional_contact;
	private $additional_contact_phone;
	private $tax_number;
	private $tb_number;
	private $birth_date;
	private $birth_place;
	private $status;
	private $creator;
	private $modifier;
	private $created;
	private $modified;
	/**
	 *
	 * @return string
	 */
	public function getId(){

		return $this->id;
	}
	
	/**
	 *
	 * @param string $id 
	 */
	public function setId($id){
		
		$this->id = $id;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCustomerType(){
	
		return $this->customer_type;
	}
	
	/**
	 *
	 * @param string $surname
	 */
	public function setCustomerType($customerType){
	
		$this->customer_type = $customerType;
		return $this;
	}
	

	/**
	 *
	 * @return string
	 */
	public function getSurname(){

		return $this->surname;
	}

	/**
	 *
	 * @param string $surname        	
	 */
	public function setSurname($surname){

		$this->surname = substr($surname, 0, 35);
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getForename(){
	
		return $this->forename;
	}
	
	/**
	 *
	 * @param string $name
	 */
	public function setForename($forename){
	
		if (!empty($forename)){
			$this->forename = substr($forename, 0, 35);
		}
		else {
			$this->forename = null;
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getZip(){

		return $this->zip;
	}

	/**
	 *
	 * @param string $zip        	
	 */
	public function setZip($zip){

		if (!empty($zip)){
			if (!preg_match("/^[0-9]{4}$/", $zip)){
				throw new InvalidArgumentException("Érvénytelen ZIP kód!");
			}
			$this->zip = (integer)$zip;
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCity(){

		return $this->city;
	}

	/**
	 *
	 * @param string $city        	
	 */
	public function setCity($city){

		$this->city = substr($city, 0, 35);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getStreet(){

		return $this->street;
	}

	/**
	 *
	 * @param string $street        	
	 */
	public function setStreet($street){

		$this->street = substr($street, 0, 50);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getStatus(){

		return $this->status;
	}
	
	/**
	 *
	 * @param string $status
	 */
	public function setStatus($status){
	
		$this->status = substr($status, 0, 20);
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getTaxNumber(){
	
		return $this->tax_number;
	}
	
	/**
	 *
	 * @param string $taxNumber
	 */
	public function setTaxNumber($taxNumber){
	
		if (!empty($taxNumber)){
			$this->tax_number = substr($taxNumber, 0, 20);
		}
		else {
			$this->tax_number = null;
		}
		return $this;
	}
	
	/**
	
	 * @return string
	 */
	public function getTbNumber(){
	
		return $this->tb_number;
	}
	
	/**
	 *
	 * @param string $tbNumber
	 */
	public function setTbNumber($tbNumber){
		if (!empty($tbNumber)){
			$this->tb_number = substr($tbNumber, 0, 20);
		}
		else {
			$this->tb_number = null;
		}
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getBirthPlace(){
	
		return $this->birth_place;
	}
	
	/**
	 *
	 * @param string $birthPlace
	 */
	public function setBirthPlace($birthPlace){
	
		if (!empty($birthPlace)){
			$this->birth_place = substr($birthPlace, 0, 20);
		}
		else {
			$this->birth_place = null;
		}
		return $this;
	}
	
	/**
	 *
	 * @return date
	 */
	public function getBirthDate(){
	
		return $this->birth_date;
	}
	
	/**
	 *
	 * @param date $birthPlace
	 */
	public function setBirthDate($birthDate){

		$this->birth_date = $birthDate;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCreator(){

		return $this->creator;
	}

	/**
	 *
	 * @param string $creator        	
	 */
	public function setCreator($creator){

		$this->creator = substr($creator,0, 35);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getModifier(){

		return $this->modifier;
	}

	/**
	 *
	 * @param string $modifier        	
	 */
	public function setModifier($modifier){

		$this->modifier = substr($modifier,0, 35);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCreated(){

		return $this->created;
	}

	/**
	 *
	 * @return string
	 */
	public function getModified(){

		return $this->modified;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getPhone(){
	
		return $this->phone;
	}
	
	/**
	 *
	 * @param string $phone 
	 */
	public function setPhone($phone){
		
		$regexp = Config::getContextParam("VALID_PHONE_NUMBER_REGEXP");
		if (empty($regexp)){
			Logger::warning("Nincs kitöltve a VALID_PHONE_NUMBER_REGEXP context paraméter a web.xml fájlban. Így bármilyen telefonszámomt elfogad a rendszer!");
			$regexp = "/^[0-9]{1,20}$/";
		}
	
		if (!empty($phone)){
			if (preg_match($regexp, str_replace("-", "", str_replace("/", "", $phone )))) {
				$this->phone = $phone;
			}
			
			else {
				throw new InvalidArgumentException("Érvénytelen telefonszám formátum");
			}
		}
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getQualification(){
	
		return $this->qualification;
	}
	
	/**
	 *
	 * @param string $qualification
	 */
	public function setQualification($qualification){
	
		//TODO: Check code tábla
		$this->qualification = $qualification;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getDescription(){
	
		return $this->description;
	}
	
	/**
	 *
	 * @param string $description
	 */
	public function setDescription($description){
	
		if (!empty($description)){
			$this->description = substr($description, 0, 500);
		}
		else {
			$this->description = null;
		}
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getAdditionalContact(){
	
		return $this->additional_contact;
	}
	
	/**
	 *
	 * @param string $familyCare
	 */
	public function setAdditionalContact($additionalContact){
	
		if (!empty($additionalContact)){
			$this->additional_contact = substr($additionalContact, 0, 35);
		}
		else {
			$this->additional_contact = null;
		}
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getAdditionalContactPhone(){
	
		return $this->additional_contact_phone;
	}
	
	/**
	 *
	 * @param string $familyCarePhone
	 */
	public function setAdditionalContactPhone($additionalContactPhone){
	
		$regexp = Config::getContextParam("VALID_PHONE_NUMBER_REGEXP");
		if (empty($regexp)){
			Logger::warning("Nincs kitöltve a VALID_PHONE_NUMBER_REGEXP context paraméter a web.xml fájlban. Így bármilyen telefonszámomt elfogad a rendszer!");
			$regexp = "/^[0-9]{1,20}$/";
		}
	
	
		if (!empty($additionalContactPhone)){
			if (preg_match($regexp, str_replace("-", "", str_replace("/", "", $additionalContactPhone )))) {
				$this->additional_contact_phone = $additionalContactPhone;
			}
			else {
				throw new InvalidArgumentException("Érvénytelen másodlagos telefonszám formátum");
			}
		}
		return $this;
	}
	
}

?>