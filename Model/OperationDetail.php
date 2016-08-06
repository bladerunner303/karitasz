<?php

require_once '../Util/Loader.php';

class OperationDetail implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}
	
	/**
	 * @return array<Operation>
	 */
	public function find(){
		$sql = "select 
					od.*,
					goods_type_codes.code_value goods_type_local,
					status_codes.code_value status_local
				from 
					operation_detail od
					inner join code goods_type_codes on goods_type_codes.id = od.goods_type
					inner join code status_codes on status_codes.id = od.status
				where (:id is null or od.id = :id)
				and (:operation_id is null or od.operation_id = :operation_id)
				order by order_indicator
				";
				
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':id' => $this->id,
				':operation_id' => $this->operation_id
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
		
			
		$pre = $db->prepare("insert into operation_detail 
								( id, operation_id, name, goods_type, storehouse_id, status, order_indicator) 
						 values (
								  :id, :operation_id, :name, :goods_type, :storehouse_id, :status, :order_indicator
								)");
		$params = array(
					':id' => SystemUtil::getGuid(),
					':operation_id' => $this->operation_id,
					':name' => $this->name,
					':goods_type' => $this->goods_type,
					':storehouse_id'=>$this->storehouse_id,
					':status' => $this->status,
					':order_indicator' => $this->order_indicator
		);

		$pre->execute($params);
		return $this->id;
			
	}
	
	public static function removeAll($operationId){
		$db = Data::getInstance();
		
			
		$pre = $db->prepare("delete from operation_detail where operation_id = :operation_id" );
		$params = array(
				':operation_id' =>$operationId
		);
		
		$pre->execute($params);
		
	}

	private $id;
	private $operation_id;
	private $name;
	private $goods_type;
	private $storehouse_id;
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
		
		$this->id = $id;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getOperationId(){
	
		return $this->operation_id;
	}
	
	/**
	 *
	 * @param string $operationId
	 */
	public function setOperationId($operationId){
	
		$this->operation_id = $operationId;
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
	
		$this->name = substr($name, 0, 50);
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getGoodsType(){
	
		return $this->goods_type;
	}
	
	/**
	 *
	 * @param string $goodsType
	 */
	public function setGoodsType($goodsType){
	
		$this->goods_type = $goodsType;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getStoreHouseId(){
	
		return $this->storehouse_id;
	}
	
	/**
	 *
	 * @param string $storeHouseId
	 */
	public function setStoreHouse($storeHouseId){
	
		$this->storehouse_id = $storeHouseId;
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