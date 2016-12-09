<?php

require_once '../Util/Loader.php';

class Transport implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}

	/**
	 * @param string $beginDate (yyyy-mm-dd format)
	 * @param string $endDate (yyyy-mm-dd format)
	 * @param string $text (státusz kód, szállítás kód, ügyfél név-ből keres) 
	 * @param string $operationId 
	 */
	public function find($beginDate, $endDate, $text, $operationId = NULL){
		$sql = "select 
					distinct
					t.*, 
					concat(substring(t.created, 1, 4), '/', lpad(t.id, 8, '0')) id_format,
					code_status.code_value status_local,
					concat(t.created, ' (', t.creator, ')') created_info,
					concat(t.modified, ' (', t.modifier, ')') modified_info
				from 
					transport t
				inner join code code_status on t.status = code_status.id
				left join transport_address tam on tam.transport_id = t.id
				where (:id is null or t.id = :id)
				and transport_date between :begin_date and :end_date 
				and (:text is null 
						or code_status.code_value = :text
				 		or concat(substring(t.created, 1, 4), '/', lpad(t.id, 8, '0')) = :text
						or t.id in (select transport_id from transport_address ta 
												inner join operation op on op.id = ta.operation_id
												inner join customer cu on cu.id = op.customer_id
											  where cu.id = :text or concat(cu.surname, ' ', coalesce(cu.forename)) like concat(:text, '%')
									)
					 )
				and (:operation_id is null or tam.operation_id = :operation_id)
				order by transport_date";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':begin_date' => $beginDate,
				':end_date' => $endDate,
				':text' => $text,
				':operation_id' => $operationId
		);
		
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	/**
	 * @return INT id
	 */
	public function save(){
		
		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
		
		//dátum ne legyen tegnapi (csak ha módosításnál már eleve úgy volt.)
		
		if (empty($this->id)){
		
			$sql = "insert into transport ( transport_date, status, creator, created, modifier, modified)
					values (:transport_date, :status, :creator, :created, :modifier, :modified)";
			
			$pre = $db->prepare($sql);
			$pre->bindValue(':transport_date', $this->transport_date, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':creator', $this->modifier);
			$pre->bindValue(':created', $t, PDO::PARAM_STR);
			$pre->bindValue(':modifier', $this->modifier, PDO::PARAM_STR);
			$pre->bindValue(':modified', $t, PDO::PARAM_STR);
			
			$pre->execute();
			
			$this->id = (int)$db->query("select max(id) maxid from transport")->fetch(PDO::FETCH_OBJ)->maxid;
			
			$this->saveAddresses();
			
			if ($this->status != 'ROGZITETT_TRANSPORT'){
				$this->generateAddressesItems();
			}
		}
		else {
			
			$originalFinder = new Transport();
			$originalFinder->setId($this->id);
			$originalTransports = $originalFinder->find('1990-01-01', '2100-01-01', null);
			if (count($originalTransports) != 1){
				throw new InvalidArgumentException("Nem található az eredeti tétel!");
			}
			$originalTransport = $originalTransports[0];
				
			$sql = "update transport set
						transport_date = :transport_date,
						status = :status,
						modifier = :modifier,
						modified = :modified
					where id = :id";
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_INT);
			$pre->bindValue(':transport_date', $this->transport_date, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':modifier', $this->modifier, PDO::PARAM_STR);
			$pre->bindValue(':modified', $t, PDO::PARAM_STR);
				
			$pre->execute();
			
			$this->saveAddresses();
			
			if (($originalTransport->status != $this->status) && ($originalTransport->status == 'ROGZITETT_TRANSPORT')){
				$this->generateAddressesItems();
			}
		}
				
		return $this->id;
	}
	
	private function saveAddresses(){
	
		// TransportAddress::removeAll($this->id);
		
		TransportAddress::removeMissing($this->addresses);
		
		foreach ($this->addresses as $index => $currentAddress) {
			
			
			$address = new TransportAddress();
			$address->setId(empty($currentAddress->id)? null : $currentAddress->id);
			$address->setTransportId($this->id);
			$address->setOperationId($currentAddress->operation_id);
			$address->setZip($currentAddress->zip);
			$address->setCity($currentAddress->city);
			$address->setStreet($currentAddress->street);
			$address->setDescription($currentAddress->description);
			$address->setStatus($currentAddress->status);
			$address->setOrderIndicator($currentAddress->order_indicator);
			$this->addresses[$index]->id = $address->save();
			//Transport address items save
			
			if ((isset($currentAddress->items)) && (count($currentAddress->items) > 0)){
				foreach ($currentAddress->items as $item) {
					TransportAddress::saveItem($item, $this->modifier);
				}
			}
		}
	}
	
	private function generateAddressesItems(){
		foreach ($this->addresses as $index => $currentAddress) {
			TransportAddress::generateAddressItems($currentAddress->id, $currentAddress->operation_id, $this->modifier);
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
	 * @return int
	 */
	public function getId(){
	
		return (int)$this->id;
	}
	
	/**
	 *
	 * @param int $id
	 */
	public function setId($id){
	
		if (!empty($id)){
			$this->id = (int)$id;
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