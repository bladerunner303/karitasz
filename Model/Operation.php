<?php

require_once '../Util/Loader.php';

class Operation implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}
	
	/**
	 * @return array<Operation>
	 */
	public function find($text, $limit){
		
		if (($limit == null) || ($limit < 1)) {
			$limit = 100000; //Primitiv de működő megoldás
		}
		
		$sql = "select 
					o.*,
					concat(c.surname, ' ' , coalesce(c.forename, ''), ' (', c.id, ')') customer_format,
					c.phone,
					concat(c.phone, ';', c.phone2) phones,
					concat(c.zip, ' ', c.city, ' ' , c.street) full_address_format,
					operation_type_codes.code_value operation_type_local,
					status_codes.code_value status_local,
					sender_codes.code_value sender_local,
					income_type_codes.code_value income_type_local,
					neediness_codes.code_value neediness_level_local,
					has_transport_bool.code_value has_transport_local,
					is_wait_callback_bool.code_value is_wait_callback_local,
					concat(o.created, ' (', o.creator, ')') created_info,
					concat(o.modified, ' (', o.modifier, ')') modified_info,
					concat(o.last_status_changed, ' (', o.last_status_changed_user, ')') last_status_changed_info
				from 
					operation o
					inner join customer c on c.id = o.customer_id
					inner join code operation_type_codes on operation_type_codes.id = o.operation_type
					inner join code status_codes on status_codes.id = o.status
					inner join code has_transport_bool on has_transport_bool.id = o.has_transport
					inner join code is_wait_callback_bool on is_wait_callback_bool.id = o.is_wait_callback
					left join code sender_codes on sender_codes.id = o.sender
					left join code income_type_codes on income_type_codes.id = o.income_type
					left join code neediness_codes on neediness_codes.id = o.neediness_level
				where (:id is null or o.id = :id)
				and (:customer_id is null or o.customer_id = :customer_id)
				and (
					   :text is null 
					or o.customer_id like concat(:text, '%') 
					or concat(c.surname, ' ' , coalesce(c.forename, '')) like concat('%', :text , '%')
					or o.id = :text
					) 
				and (:wait_call = 'N' or o.is_wait_callback = :wait_call)
				and (:status is null or o.status = :status)
				and (:operation_type is null or o.operation_type = :operation_type)
				order by o.id
				limit :limit
				";
				
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$waitCallParam = ($this->isWaitCallback())? 'Y':'N';
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':customer_id', $this->customer_id, PDO::PARAM_STR);
		$pre->bindValue(':text', $text, PDO::PARAM_STR);
		$pre->bindValue(':wait_call', $waitCallParam, PDO::PARAM_STR);
		$pre->bindValue(':status', $this->status, PDO::PARAM_STR);
		$pre->bindValue(':operation_type', $this->operation_type, PDO::PARAM_STR);
		$pre->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
		
		$pre->execute();
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	public function findByDetails($goodsType){
		$sql = "select 
            	o.id,
                concat(c.surname, ' ' , coalesce(c.forename, ''), ' (', c.id, ')') customer_format,
                concat(c.zip, ' ', c.city, ' ' , c.street) full_address_format, 
                c.qualification,
                code_qualification_local.code_value qualification_local,
                o.created,
                date_format(o.created, '%Y-%m-%d') created_date,
                od.id operation_detail_id,
                od.name
			   from 
			   	operation o,
			   	operation_detail od,
                customer c,
                code code_qualification_local
			   where o.id = od.operation_id
               and c.id = o.customer_id
               and c.qualification = code_qualification_local.id
               and c.status != 'TILTOTT'
               and o.status != 'BEFEJEZETT'
               and c.status = 'AKTIV'
               and o.operation_type = :operation_type
               and od.goods_type = :goods_type
               order by c.qualification, o.created
			   limit 10";
		
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$pre->bindValue(':operation_type', $this->operation_type, PDO::PARAM_STR);
		$pre->bindValue(':goods_type', $goodsType, PDO::PARAM_STR);
		
		$pre->execute();
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	/**
	 * @return string
	 */
	public function save(){

		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
		
		$customerFinder = new Customer();
		$customerFinder->setId($this->customer_id);
		$customers = $customerFinder->find(null, 1);
		if (count($customers) == 0){
			throw new Exception("Nem található az ügyfél");
		}
		$customer = $customers[0];
		
		if (($customer->customer_type == 'KERVENYEZO') && ($this->getOperationType() != 'KERVENYEZES')){
			throw new Exception("Kérvényező ügyfél csak kérvényt adhat be!");
		}
		if (($customer->customer_type == 'FELAJANLO') && ($this->getOperationType() == 'KERVENYEZES')){
			throw new Exception("Felajánló ügyfél csak felajánlást adhat be!");
		}
		
		$pre = $db->prepare ("	select 
									count(*) cnt 
								from 
									operation 
								where status != 'BEFEJEZETT' 
								and customer_id = :customer_id
								 and id != coalesce(:id, '') " );
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':customer_id', $this->customer_id, PDO::PARAM_STR);
		$pre->execute();
		if ($pre->fetch(PDO::FETCH_OBJ)->cnt != '0'){
			throw new Exception("Az ügyfélnek már van másik folyamatban lévő kérvénye! Kérlek módostsd inkább azt!");	
		}
		
		if (empty($this->id)){
			
			if ($customer->qualification == 'TILTOTT'){
				throw new Exception("Tiltott státuszú ügyfél részére kérvény vagy felajánlás nem rögzíthető!");
			}
			
			$pre = $db->prepare("insert into operation 
								( operation_type, has_transport, is_wait_callback, customer_id, status, description, neediness_level,
								  sender, income_type, income, others_income, creator, created, modifier, modified, last_status_changed, last_status_changed_user) 
						 values (
								  :operation_type, :has_transport, :is_wait_callback, :customer_id, :status, :description, :neediness_level,
								  :sender, :income_type, :income, :others_income, :creator, :created, :modifier, :modified, :last_status_changed, :last_status_changed_user 
								)");
			$params = array(
					':operation_type' => $this->operation_type,
					':has_transport' => ($this->hasTransport()? 'Y': 'N'),
					':is_wait_callback' => ($this->isWaitCallback()?'Y': 'N'),
					':customer_id'=>$this->customer_id,
					':status'=>$this->status,
					':description'=>$this->description,
					':neediness_level'=>$this->neediness_level,
					':sender' => $this->sender,
					':income_type' => $this->income_type,
					':income' => $this->income,
					':others_income' => $this->others_income,
					':creator'=>$this->modifier,
					':modifier'=>$this->modifier,
					':created'=> $t,
					':modified'=> $t,
					':last_status_changed' => $t,
					':last_status_changed_user' => $this->modifier,
			);

			$pre->execute($params);
			
			$this->id = $db->query("select max(id) maxid from operation")->fetch(PDO::FETCH_OBJ)->maxid;
			$this->saveOperationDetails();

		}
		else {

			$findOperation = new Operation();
			$findOperation->setId( $this->id );
			$originalList = $findOperation->find(null,1);
			$original = new Operation();
			SystemUtil::cast($original, $originalList[0]);
			
			if ($this->isChanged($original)){
				
					if ($original->status != $this->status){
						$this->last_status_changed_user = $this->modifier;
						$this->last_status_changed = $t;
					}
					else {
						$this->last_status_changed_user = $original->last_status_changed_user;
						$this->last_status_changed = $original->last_status_changed;
					}
				
					$pre = $db->prepare("update operation
							set
							has_transport = :has_transport,
							is_wait_callback = :is_wait_callback,
							customer_id = :customer_id,
							status = :status,
							description = :description,
							neediness_level = :neediness_level,
							sender = :sender,
							income_type = :income_type,
							income = :income,
							others_income = :others_income,
							modifier = :modifier,
							modified = :modified,
							last_status_changed = :last_status_changed,
							last_status_changed_user = :last_status_changed_user
							where
							id = :id
							");
					
					$params = array(
							':has_transport' => ($this->hasTransport()? 'Y': 'N'),
							':is_wait_callback' => ($this->isWaitCallback()?'Y': 'N'),
							':customer_id'=>$this->customer_id,
							':status'=>$this->status,
							':description'=>$this->description,
							':neediness_level'=>$this->neediness_level,
							':sender' => $this->sender,
							':income_type' => $this->income_type,
							':income' => $this->income,
							':others_income' => $this->others_income,
							':modifier'=>$this->modifier,
							':modified'=> $t,
							':last_status_changed' => $this->last_status_changed,
							':last_status_changed_user' => $this->last_status_changed_user,
							':id' => $this->id
					);
						
					$pre->execute($params);
					$this->saveOperationDetails();
				
			}
		}
		return $this->id;

	}
	
	private function isChanged($original){

		if (empty($this->id)){
			return false;
		}
			
		if (($original->hasTransport()!= $this->hasTransport())
		||  ($original->isWaitCallback() != $this->isWaitCallback())
		||	($original->getCustomerId() != $this->getCustomerId())
		||	($original->getStatus() != $this->getStatus())
		||	($original->getDescription() != $this->getDescription())
		||	($original->getNeedinessLevel() != $this->getNeedinessLevel())
		||  ($original->getSender() != $this->getSender())
		||	($original->getDescription() != $this->getDescription())
		||	($original->getIncomeType() != $this->getIncomeType())
		||	($original->getIncome() != $this->getIncome())
		||	($original->getOthersIncome() != $this->getOthersIncome())
		){
			return true;			
		}
		
		$finderOperationDetails = new OperationDetail();
		$finderOperationDetails->setOperationId($this->getId());
		$originalOperationDetails = $finderOperationDetails->find();
		
		if (count($originalOperationDetails) != count($this->operationDetails)){
			return true;
		}
		
		foreach ($this->operationDetails as $index => $operationDetail) {
			if (($operationDetail->name != $originalOperationDetails[$index]->name) 
			||  ($operationDetail->goods_type != $originalOperationDetails[$index]->goods_type)
			||  ($operationDetail->status != $originalOperationDetails[$index]->status)
			||  ($operationDetail->detail_id != $originalOperationDetails[$index]->detail_id)
			){
				return true;	
			}
		}
		return false;
	}
	
	private function saveOperationDetails(){
		
		OperationDetail::removeAll($this->id);
		foreach ($this->operationDetails as $index => $operationDetail) {
			$detail = new OperationDetail();
			$detail->setOperationId($this->id);
			$detail->setName($operationDetail->name);
			$detail->setGoodsType($operationDetail->goods_type);
			$detail->setStatus($operationDetail->status);
			$detail->setOrderIndicator($index);
			$detail->setDetailId($operationDetail->detail_id);
			$detail->save();
		}
	}
	
	/**
	 * @param File $file
	 */
	public function addOperationAttachment($file){
		
		//TODO: database transaction
		
		$db = Data::getInstance();
		$fileMetaDataId = $file->save();
		$pre = $db->prepare ( "insert into operation_file (operation_id, file_meta_data_id) values (:operation_id, :file_meta_data_id)");
		$pre->bindValue(':operation_id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':file_meta_data_id', $file->getId(), PDO::PARAM_STR);
		$pre->execute();
		
	}
	
	public function listOperationFiles(){
		$sql = "select 
						fmd.*,
						round(fmd.size/1024/1024, 3) size_in_mb,
						concat(fmd.created, ' (', fmd.creator, ')') created_info
				from 
					file_meta_data fmd,
					operation_file of
				where fmd.id = of.file_meta_data_id
				and of.operation_id = :id
				order by fmd.created";
		
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}

	private $id;
	private $operation_type;
	private $has_transport;
	private $customer_id;
	private $is_wait_callback;
	private $description;
	private $neediness_level;
	private $sender;
	private $income_type;
	private $income;
	private $others_income;
	private $status;
	private $creator;
	private $modifier;
	private $created;
	private $modified;
	private $last_status_changed;
	private $last_status_changed_user;
	private $operationDetails;
	
	/**
	 *
	 * @return array
	 */
	public function getOperationDetails(){
	
		return $this->operationDetails;
	}
	
	/**
	 *
	 * @param array $operationDetails
	 */
	public function setOperationDetails($operationDetails){
	
		$this->operationDetails = $operationDetails;
		return $this;
	}
	
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
	public function getOperationType(){
	
		return $this->operation_type;
	}
	
	/**
	 *
	 * @param string $operationType
	 */
	public function setOperationType($operationType){
	
		$this->operation_type = $operationType;
		return $this;
	}
	
	/**
	 *
	 * @return boolean 
	 */
	public function hasTransport(){
	
		return ($this->has_transport == 'Y');
	}
	
	/**
	 *
	 * @param boolean or string $hasTransport
	 */
	public function setHasTransport($hasTransport){	
		$this->has_transport = (($hasTransport == 'Y') || ($hasTransport === true));
		return $this;
	}
	
	/**
	 *
	 * @return boolean
	 */
	public function isWaitCallback(){
	
		return ($this->is_wait_callback == 'Y');
	}
	
	/**
	 *
	 * @param boolean or string $hasTransport
	 */
	public function setIsWaitCallback($isWaitCallback){
		$this->is_wait_callback = (($isWaitCallback == 'Y') || ($isWaitCallback === true));
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
	
		$this->customer_id = $customerId;
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
	
		$this->description = empty($description)? null : substr($description, 0, 500);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getNeedinessLevel(){
	
		return $this->neediness_level;
	}
	
	/**
	 *
	 * @param string $needinessLevel
	 */
	public function setNeedinessLevel($needinessLevel){
	
		$this->neediness_level = $needinessLevel;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getSender(){
	
		return $this->sender;
	}
	
	/**
	 *
	 * @param string $sender
	 */
	public function setSender($sender){
	
		$this->sender = $sender;
		return $this;
	}
	
	
	/**
	 *
	 * @return string
	 */
	public function getIncomeType(){
	
		return $this->income_type;
	}
	
	/**
	 *
	 * @param string $incomeType
	 */
	public function setIncomeType($incomeType){
	
		$this->income_type = $incomeType;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getIncome(){
	
		return $this->income;
	
	}
	
	/**
	 *
	 * @param string $income
	 */
	public function setIncome($income){

		$this->income = $income;
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getOthersIncome(){
	
		return $this->others_income;
	}
	
	/**
	 *
	 * @param string $others_income
	 */
	public function setOthersIncome($others_income){

		$this->others_income = $others_income;
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
	
		$this->status = empty($status)? null: substr($status, 0, 20);
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
	 * @param string $closingUser
	 */
	public function setLastStatusChangedUser($lastStatusChangedUser){
	
		$this->last_status_changed_user = substr($lastStatusChangedUser,0, 35);
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getLastStatusChangedUser(){
	
		return $this->last_status_changed_user;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getLastStatusChanged(){
	
		return $this->last_status_changed;
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