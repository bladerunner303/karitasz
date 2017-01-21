<?php

require_once '../Util/Loader.php';

class TransportAddress implements JsonSerializable {
	
	
	public function jsonSerialize() {
		return get_object_vars($this);
	}
	
	public function find(){
		$sql = "select 
					ta.* ,
					concat(ta.zip, ' ', ta.city, ' ' , ta.street) address_format,
					concat(c.surname, ' ' , coalesce(c.forename, ''), ' (', c.id, ')') customer_format,
					o.description operation_description,
					concat(c.phone, ',', coalesce(c.phone2, '')) customer_phone,
					status_codes.code_value status_local
				from 
					transport_address ta
				inner join operation o on o.id = ta.operation_id
				inner join customer c on c.id = o.customer_id
				inner join code status_codes on status_codes.id = ta.status
				where (:id is null or ta.id = :id)
				and (:transport_id is null or ta.transport_id = :transport_id)
				order by ta.order_indicator";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':transport_id'=> $this->transport_id
		);
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	public static function findAddressItems($transportAddressId){
		$sql = "select 
					ai.*,
					od.goods_type,
					od.name,
					concat(goods_type_codes.code_value, ' (' , od.name , ') ') name_format,
					goods_type_codes.code_value goods_type_local,
					status_codes.code_value status_local
				from transport_address_item ai
				inner join operation_detail od on ai.operation_detail_id = od.id
				inner join code goods_type_codes on goods_type_codes.id = od.goods_type
				inner join code status_codes on status_codes.id = ai.status
				where transport_address_id = :transport_address_id
				order by od.order_indicator";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':transport_address_id'=> $transportAddressId
		);
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function save(){
	
		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
			
		if (empty($this->id)){
	
			$this->id = SystemUtil::getGuid();
			$sql = "insert into transport_address (id,operation_id,transport_id,zip,city,street,description, status, order_indicator)
						values (:id,:operation_id,:transport_id,:zip,:city,:street,:description, :status, :order_indicator)";
				
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':operation_id', $this->operation_id, PDO::PARAM_STR);
			$pre->bindValue(':transport_id', $this->transport_id, PDO::PARAM_INT);
			$pre->bindValue(':zip', $this->zip, PDO::PARAM_STR);
			$pre->bindValue(':city', $this->city, PDO::PARAM_STR);
			$pre->bindValue(':street', $this->street, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':order_indicator', $this->order_indicator, PDO::PARAM_INT);
				
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
									status = :status,
									order_indicator = :order_indicator
								where id = :id";
			$pre = $db->prepare($sql);
			$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
			$pre->bindValue(':operation_id', $this->operation_id, PDO::PARAM_STR);
			$pre->bindValue(':transport_id', $this->transport_id, PDO::PARAM_INT);
			$pre->bindValue(':zip', $this->zip, PDO::PARAM_STR);
			$pre->bindValue(':city', $this->city, PDO::PARAM_STR);
			$pre->bindValue(':street', $this->street, PDO::PARAM_STR);
			$pre->bindValue(':description', $this->description, PDO::PARAM_STR);
			$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
			$pre->bindValue(':order_indicator', $this->order_indicator, PDO::PARAM_INT);
			
			$pre->execute();
		}
	
		return $this->id;
	}
	
	public static function saveItem($item, $user){
		$sql = "update 
					transport_address_item 
				set
					status = :status,
					modifier = :modifier,
					modified = :modified
				where 
					id = :id";
		
		$t = SystemUtil::getCurrentTimestamp();
		
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		
		$pre->bindValue(':status', $item->status, PDO::PARAM_STR);
		$pre->bindValue(':modifier', $user, PDO::PARAM_STR);
		$pre->bindValue(':modified', $t, PDO::PARAM_STR);
		$pre->bindValue(':id', $item->id, PDO::PARAM_STR);
		
		$pre->execute();
		
		
		if ($item->status == 'BEFEJEZETT_TRANSPORT'){
			$operationDetailFinder = new OperationDetail();
			$operationDetailFinder->setId($item->operation_detail_id);
			$operationDetails = $operationDetailFinder->find();
			if (count($operationDetails)== 0) {
				Logger::warning("Nem található az itemhez tartozó operation detail. operation_detail_id: " . $item->operation_detail_id);
				return;
			}
			
			$operationDetail = new OperationDetail();
			$operationDetail = SystemUtil::cast($operationDetail, $operationDetails[0]);
			$operationDetail->setDetailFiles($operationDetail->getDetailFiles());
			$operationDetail->setStatus('BEFEJEZETT' );
			$operationDetail->save();
		}
		
	}
	
	public function remove(){
		$db = Data::getInstance();
		
		$sql = "delete from transport_address_item where transport_address_id = :id";
		$pre = $db->prepare($sql);
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		
		$sql = "delete from transport_address where id = :id";
		$pre = $db->prepare($sql);
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		
		return true;
	}
	
	public static function removeAll($transportId){
		$db = Data::getInstance();
		$sql = "delete from transport_address where transport_id = :transport_id";
		$pre = $db->prepare($sql);
		$pre->bindValue(':transport_id', $transportId, PDO::PARAM_STR);
		
		$pre->execute();
	}
	
	public static function removeMissing($addressArray){
		
		if (count($addressArray) == 0){
			return;
		}
		
		if (empty($address[0]->transport_id)){
			return;
		}
		$finder = new Transport();
		$finder->setId($addressArray[0]->transport_id);
		$currentAddresses = $finder->find('1990-01-01', '2100-01-01', null);
		
		foreach ($currentAddresses as $index => $current) {
			
			foreach ($addressArray as $key => $address) {
			
				if ($current->id == $address->id){
					break;
				}
				
				if (count($addressArray) == $key+1){
					$remover = new TransportAddress();
					$remover->setId($current->id);
					$remover->remove();
				}
			}	
		}
	}
	
	public static function generateAddressItems($addressId, $operationId, $user){
		$db = Data::getInstance();
		$t = SystemUtil::getCurrentTimestamp();
		$sql = "insert into transport_address_item
					(id, transport_address_id, operation_detail_id, status, creator, created, modifier, modified)
				select
					uuid(),
					:address_id,
					od.id,
					'ROGZITETT_TRANSPORT',
					:user,
					:t,
					:user,
					:t
				from operation_detail od
				where od.operation_id = :operation_id";
		$pre = $db->prepare($sql);
		$pre->bindValue(':address_id', $addressId, PDO::PARAM_STR);
		$pre->bindValue(':operation_id', $operationId, PDO::PARAM_STR);
		$pre->bindValue(':user', $user, PDO::PARAM_STR);
		$pre->bindValue(':t', $t, PDO::PARAM_STR);
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
	private $order_indicator;
	
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
	 * @return int
	 */
	public function getTransportId(){
	
		return (int)$this->transport_id;
	}
	
	/**
	 *
	 * @param int $transportId
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
	
	/**
	 *
	 * @return int
	 */
	public function getOrderIndicator(){
	
		return $this->order_indicator;
	}
	
	/**
	 *
	 * @param int $orderIndicator
	 */
	public function setOrderIndicator($orderIndicator){
	
		$this->order_indicator = $orderIndicator;
		return $this;
	}
	
}

?>