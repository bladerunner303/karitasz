<?php

require_once '../Util/Loader.php';

class Transport implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}

	public function find($beginDate, $endDate){
		$sql = "select 
					* 
				from 
					transport 
				where (:id is null or id = :id)
				and transport_date between :begin_date and :end_date 
				order by transport_date";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':begin_date' => $beginDate,
				':end_date' => $endDate
		);
		
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function save(){
		
		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
		
		//dátum ne legyen tegnapi (csak ha módosításnál már eleve úgy volt.)
		
		if (empty($this->id)){
		
			$this->id = SystemUtil::getGuid();
			$sql = "insert into transport (id, transport_date, status, creator, created, modifier, modified)
					values (:id, :transport_date, :status, :creator, :created, :modifier, :modified)";
			
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':transport_date', $this->transport_date, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':creator', $this->modifier);
			$pre->bindValue(':created', $t, PDO::PARAM_STR);
			$pre->bindValue(':modifier', $this->modifier, PDO::PARAM_STR);
			$pre->bindValue(':modified', $t, PDO::PARAM_STR);
			
			$pre->execute();
			
			$this->saveAddresses();
		}
		else {
			$sql = "update transport set
						transport_date = :transport_date,
						status = :status,
						modifier = :modifier,
						modified = :modified
					where id = :id";
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':transport_date', $this->transport_date, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':modifier', $this->modifier, PDO::PARAM_STR);
			$pre->bindValue(':modified', $t, PDO::PARAM_STR);
				
			$pre->execute();
			
			$this->saveAddresses();
		}
		
		
		
		return $this->id;
	}
	
	private function saveAddresses(){
	
		TransportAddress::removeAll($this->id);
		foreach ($this->addresses as $index => $currentAddress) {
			$address = new TransportAddress();
			$address->setTransportId($this->id);
			$address->setOperationId($currentAddress->operation_id);
			$address->setZip($currentAddress->zip);
			$address->setCity($currentAddress->city);
			$address->setStreet($currentAddress->street);
			$address->setDescription($currentAddress->description);
			$address->setStatus($currentAddress->status);
			$address->save();
		}
	}
	
	
	private $id;
	private $transport_date;
	private $status ;
	private $modifier;
	private $modified;
	private $creator;
	private $created;
	private $addresses;
	
	
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
	public function getTransportDate(){
	
		return $this->transport_date;
	}
	
	/**
	 *
	 * @param string $transportDate
	 */
	public function setTransportDate($transportDate){
	
		if (!empty($transportDate)){
			$this->transport_date = $transportDate;
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
	
		if (!empty($modifier)){
			$this->modifier = $modifier;
		}
		return $this;
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
	 * @param string $modified
	 */
	public function setModified($modified){
	
		if (!empty($modified)){
			$this->modified = $modified;
		}
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
	
		if (!empty($creator)){
			$this->creator = $creator;
		}
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
	 * @param string $created
	 */
	public function setCreated($created){
	
		if (!empty($created)){
			$this->created = $created;
		}
		return $this;
	}
	
	/**
	 *
	 * @return array
	 */
	public function getAddresses(){
	
		return $this->addresses;
	}
	
	/**
	 *
	 * @param array $addresses
	 */
	public function setAddresses($addresses){
	
		$this->addresses = $addresses;
		return $this;
	}
		
}

?>