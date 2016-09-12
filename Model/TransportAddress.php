<?php

require_once '../Util/Loader.php';

class TransportAddress implements JsonSerializable {
	
	
	public function jsonSerialize() {
		return get_object_vars($this);
	}
	
	public function find(){
		$sql = "select * from 
					transport_address 
				where (:id is null or id = :id)
				and (:transport_id is null or transport_id = :transport_id)
				order by zip";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':transport_id'=> $this->transport_id
		);
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	public function save(){
	
		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
			
		if (empty($this->id)){
	
			$this->id = SystemUtil::getGuid();
			$sql = "insert into transport_address (id,operation_id,transport_id,zip,city,street,description, status)
						values (:id,:operation_id,:transport_id,:zip,:city,:street,:description, :status)";
				
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':operation_id', $this->operation_id, PDO::PARAM_STR);
			$pre->bindValue(':transport_id', $this->transport_id, PDO::PARAM_STR);
			$pre->bindValue(':zip', $this->zip, PDO::PARAM_STR);
			$pre->bindValue(':city', $this->city, PDO::PARAM_STR);
			$pre->bindValue(':street', $this->street, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
				
			$pre->execute();
		}
		else {
			$sql = "update transport_address set
									id= :id,
									operation_id= :operation_id,
									transport_id= :transport_id,
									zip= :zip,
									city= :city,
									street= :street,
									description= :description,
									status = :status
								where id = :id";
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':operation_id', $this->operation_id, PDO::PARAM_STR);
			$pre->bindValue(':transport_id', $this->transport_id, PDO::PARAM_STR);
			$pre->bindValue(':zip', $this->zip, PDO::PARAM_STR);
			$pre->bindValue(':city', $this->city, PDO::PARAM_STR);
			$pre->bindValue(':street', $this->street, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			
			$pre->execute();
		}
	
		return $this->id;
	}
	
	public static function removeAll($transportId){
		$db = Data::getInstance();
		$sql = "delete from transport_address where transport_id = :transport_id";
		$pre = $db->prepare($sql);
		$pre->bindValue(':transport_id', $transportId, PDO::PARAM_STR);
		
		$pre->execute();
	}
	
	
	private $id;
	private $operation_id;
	private $transport_id;
	private $zip;
	private $city;
	private $street;
	private $phone;
	private $description;
	private $status;
	
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
	 * @return integer
	 */
	public function getOperationId(){
	
		return $this->operation_id;
	}
	
	/**
	 *
	 * @param integer $operationId
	 */
	public function setOperationId($operationId){
	
		if (!empty($operationId)){
			$this->operation_id = (int)$operationId;
		}
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getTransportId(){
	
		return $this->transport_id;
	}
	
	/**
	 *
	 * @param string $transportId
	 */
	public function setTransportId($transportId){
	
		if (!empty($transportId)){
			$this->transport_id = $transportId;
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
			$this->zip = substr($zip, 0, 4);
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
	
		if (!empty($city)){
			$this->city = substr($city, 0, 35);
		}
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
	
		if (!empty($street)){
			$this->street = substr($street, 0, 35);
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
			$this->description = substr($description, 0, 500);
		}
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
	
		if (!empty($status)){
			$this->status = $status;
		}
		return $this;
	}
	
	
}

?>