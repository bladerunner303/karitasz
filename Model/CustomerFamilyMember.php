<?php

require_once '../Util/Loader.php';

class CustomerFamilyMember implements JsonSerializable {
	
	
	public function jsonSerialize() {
		return get_object_vars($this);
	}
	
	public function find(){
		$sql = "select * 
				from 
					customer_family_member 
				where (:id is null or id = :id)
				and (customer_id = :customer_id)
		";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':customer_id' => $this->customer_id
		);
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function save($modifier){
	
		$db = Data::getInstance();
			
		if (empty($this->id)){
			$this->logChange($modifier, 'MEMBER_NEW');
			$this->id = SystemUtil::getGuid();
			$sql = "insert into  customer_family_member  (id, customer_id, name, family_member_customer, family_member_type, birth_date, description)
			values (:id,:customer_id,:name,:family_member_customer,:family_member_type,:birth_date,:description)";
				
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':customer_id', $this->customer_id, PDO::PARAM_STR);
			$pre->bindValue(':name', $this->name, PDO::PARAM_STR);
			$pre->bindValue(':family_member_customer', $this->family_member_customer, PDO::PARAM_STR);
			$pre->bindValue(':family_member_type', $this->family_member_type, PDO::PARAM_STR);
			$pre->bindValue(':birth_date', $this->birth_date, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
				
			$pre->execute();
			
		}
		else {
			$this->logChange($modifier, 'MEMBER_MODIFY');
			$sql = "update customer_family_member
						set 
						name = :name, 
						family_member_customer = :family_member_customer,
						family_member_type = :family_member_type, 
						birth_date = :birth_date, 
						description = :description
					where id = :id";
			
			$pre = $db->prepare($sql);
			$pre->bindValue(':name', $this->name, PDO::PARAM_STR);
			$pre->bindValue(':family_member_customer', $this->family_member_customer, PDO::PARAM_STR);
			$pre->bindValue(':family_member_type', $this->family_member_type, PDO::PARAM_STR);
			$pre->bindValue(':birth_date', $this->birth_date, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
				
			$pre->execute();
				
		}
		return $this->id;
	}
	
	public function remove($modifier){
		$this->logChange($modifier, 'MEMBER_REMOVE');
		$db = Data::getInstance();
		$pre = $db->prepare("delete from  customer_family_member where id = :id");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		
	}
	
	/**
	 * @param string $modifier
	 * @param string $type (MEMBER_REMOVE, MEMBER_MODIFY, MEMBER_NEW)
	 */
	private function logChange($modifier, $type){
		$t = SystemUtil::getCurrentTimestamp();
		$id = SystemUtil::getGuid();
		$new = trim($this->name . ' ' . $this->family_member_customer . ' ' . $this->birth_date . ' ' . $this->family_member_type . ' ' . $this->description);
		
		$db = Data::getInstance();
		
		$sql= '';
		if (empty($this->id)){
			$sql = "insert into customer_history
				(id, customer_id, data_type, old_value, new_value, creator, created)
				values 
				(:history_id, :customer_id, :type, null, :new, :modifier, :t)";
		}
		else {
			$sql = "insert into customer_history
				(id, customer_id, data_type, old_value, new_value, creator, created)
				SELECT
				:history_id ,
				fm.customer_id,
				:type,
				concat( fm.name, ' ' , 
						coalesce(fm.family_member_customer, ''), ' ', 
						coalesce(fm.birth_date, ''), ' ' , 
						fm.family_member_type, ' ', 
						coalesce(fm.description, '')),
				:new,
				:modifier,
				:t
				from
				customer_family_member fm
				where id = :id
				";
		}
		
		$pre = $db->prepare($sql);
		$pre->bindValue(':history_id', $id, PDO::PARAM_STR);
		$pre->bindValue(':type', $type, PDO::PARAM_STR);
		$pre->bindValue(':new', $new, PDO::PARAM_STR);
		$pre->bindValue(':modifier', $modifier, PDO::PARAM_STR);
		$pre->bindValue(':t', $t, PDO::PARAM_STR);
		if (empty($this->id)){
			$pre->bindValue(':customer_id', $this->customer_id, PDO::PARAM_STR);
		}
		else {
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		}
		$pre->execute();
		
	}
	
	private static function removeAll($customerId){
		$db = Data::getInstance();
		$pre = $db->prepare("delete from  customer_family_member where customer_id = :customer_id");
		$pre->bindValue(':customer_id', $customer_id, PDO::PARAM_STR);
		$pre->execute();
		
	}
	
	private $id;
	private $customer_id;
	private $name;
	private $family_member_customer;
	private $family_member_type ;
	private $birth_date;
	private $description;
	
	
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
	
		if (!empty($id)){
			$this->id = $id;
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getCustomerId(){
	
		return $this->customer_id;
	}
	
	/**
	 *
	 * @param string $customerId
	 */
	public function setCustomerId($customerId){
	
		if (!empty($customerId)){
			$this->customer_id = $customerId;
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getName(){
	
		return $this->name;
	}
	
	/**
	 *
	 * @param string $name
	 */
	public function setName($name){
	
		if (!empty($name)){
			$this->name = substr($name,0,50);
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getFamilyMemberCustomer(){
	
		return $this->family_member_customer;
	}
	
	/**
	 *
	 * @param string $familyMemberCustomer
	 */
	public function setFamilyMemberCustomer($familyMemberCustomer){
	
		if (!empty($familyMemberCustomer)){
			$this->family_member_customer = $familyMemberCustomer;
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getFamilyMemberType(){
	
		return $this->family_member_type;
	}
	
	/**
	 *
	 * @param string $familyMemberType
	 */
	public function setFamilyMemberType($familyMemberType){
	
		if (!empty($familyMemberType)){
			$this->family_member_type = $familyMemberType;
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getBirthDate(){
	
		return $this->birth_date;
	}
	
	/**
	 *
	 * @param string $birthDate
	 */
	public function setBirthDate($birthDate){
	
		if (!empty($birthDate)){
			$this->birth_date = $birthDate;
		}
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
			$this->description = substr($description, 0, 255);
		}
		return $this;
	}
	
	
	
}

?>