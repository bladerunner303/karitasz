<?php

require_once '../Util/Loader.php';

class OperationDetail implements JsonSerializable {

	public function jsonSerialize():array {
		return get_object_vars($this);
	}

	/**
	 * @return array<Operation>
	 */
	public function find(){
		$sql = "select
					od.*,
					goods_type_codes.code_value goods_type_local,
					status_codes.code_value status_local,
					concat(o.id, '/', o.customer_id, ' ', odp.name) related_operation_detail
				from
					operation_detail od
					inner join code goods_type_codes on goods_type_codes.id = od.goods_type
					inner join code status_codes on status_codes.id = od.status
					left join operation_detail odp on odp.id = od.detail_id
					left join operation o on odp.operation_id = o.id
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

		if (empty($this->id)){

			$this->id = SystemUtil::getGuid();


			$pre = $db->prepare("insert into operation_detail
									( id, operation_id, name, goods_type, storehouse_id, status, order_indicator, detail_id)
							 values (
									  :id, :operation_id, :name, :goods_type, :storehouse_id, :status, :order_indicator, :detail_id
									)");
			$params = array(
						':id' => $this->id,
						':operation_id' => $this->operation_id,
						':name' => $this->name,
						':goods_type' => $this->goods_type,
						':storehouse_id'=>$this->storehouse_id,
						':status' => $this->status,
						':order_indicator' => $this->order_indicator,
						':detail_id' => $this->detail_id
			);

			$pre->execute($params);

			if (!empty($this->detail_id)){
				$pre = $db->prepare("update operation_detail set detail_id = :detail_id where id=:id");
				$params = array(
						':id' => $this->detail_id,
						':detail_id' => $this->id
				);

				$pre->execute($params);

			}
		}
		else {
			$pre = $db->prepare("update operation_detail set
									name = :name,
									goods_type = :goods_type,
									storehouse_id = :storehouse_id,
									status = :status,
									order_indicator = :order_indicator,
									detail_id = :detail_id
						 			where id=:id");
			$params = array(
					':id' => $this->id,
					':name' => $this->name,
					':goods_type' => $this->goods_type,
					':storehouse_id'=>$this->storehouse_id,
					':status' => $this->status,
					':order_indicator' => $this->order_indicator,
					':detail_id' => $this->detail_id
			);

			$pre->execute($params);

			//Ha befejezett az összes operation_detail a fejet is befejezettre állítjuk.
			if ($this->status == 'BEFEJEZETT'){
				$pre = $db->prepare("select count(*) cnt from operation_detail where operation_id= :id and status != 'BEFEJEZETT'");
				$params = array(':id' => $this->operation_id);
				$pre->execute($params);
				if ((int)$pre->fetch(PDO::FETCH_OBJ)->cnt == 0) {


					$pre = $db->prepare("update operation set
											status = 'BEFEJEZETT',
											transport_date = (
												select t.transport_date from
													transport t,
													transport_address ta
												where t.id = ta.transport_id
												and ta.operation_id = :id)
											where id=:id");
					$params = array(':id' => $this->operation_id);
					$pre->execute($params);
				};
			}

		}

		$sql = "delete from operation_detail_file where operation_detail_id = :id";
		$pre = $db->prepare($sql);
		$params = array(':id' => $this->id);
		$pre->execute($params);

		foreach ($this->detail_files as  $fileId) {
			$sql = "insert into operation_detail_file (operation_detail_id, file_meta_data_id)
					values (:operation_detail_id, :file_meta_data_id)";
			$pre = $db->prepare($sql);
			$params = array(':operation_detail_id' => $this->id,
							':file_meta_data_id' => $fileId );
			$pre->execute($params);

		}

		return $this->id;

	}

	public function remove(){
		$db = Data::getInstance();

		$pre = $db->prepare("update operation_detail
				set detail_id = null
				where detail_id = :id");
		$params = array(':id' =>$this->id);
		$pre->execute($params);

		$pre = $db->prepare("delete from operation_detail where id = :id" );
		$pre->execute($params);

	}

	public static function removeAll($operationId){
		$db = Data::getInstance();

		$pre = $db->prepare("update operation_detail
							 set detail_id = null
							 where id in (select x.detail_id from (select * from operation_detail) as x where x.operation_id = :operation_id)");
		$params = array(
				':operation_id' =>$operationId
		);

		$pre->execute($params);


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
	private $detail_id;
	private $detail_files;
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
	public function getDetailId(){

		return $this->detail_id;
	}

	/**
	 *
	 * @param string $detailId
	 */
	public function setDetailId($detailId){

		$this->detail_id = $detailId;
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


	/**
	 *
	 * @return array
	 */
	public function getDetailFiles(){

		if (empty($this->detail_files)){
			//lazy load
			$this->detail_files = array();
			$sql = "select file_meta_data_id from operation_detail_file where operation_detail_id = :operation_detail_id";
			$db = Data::getInstance();
			$pre = $db->prepare($sql);
			$params = array(':operation_detail_id' => $this->id);
			$pre->execute($params);
			$detailFiles = $pre->fetchAll(PDO::FETCH_OBJ);
			foreach ($detailFiles as $detailFile) {
				array_push($this->detail_files, $detailFile->file_meta_data_id)	;
			}
		}
		return $this->detail_files;
	}

	/**
	 *
	 * @param array $detailFiles (csak a file meta id ket egy tömbben)
	 */
	public function setDetailFiles($detailFiles){

		$this->detail_files = $detailFiles;
		return $this;
	}

}

?>
