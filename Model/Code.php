<?php

require_once '../Util/Loader.php';

class Code implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}

	public static function listCode($codeType){
		$sql = "select * from code where code_type = :code_type order by id";
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$params = array(
				':code_type' => $codeType
		);
		
		$pre->execute($params);
		return $pre->fetchAll(PDO::FETCH_OBJ);
	}
	
	private $id;
	private $codeType;
	private $codeValue ;
	private $modifier;
	private $modified;

	/**
	 *
	 * @return integer
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
			if (!preg_match("/^[0-9]{0,6}$/", $id)){
				throw new InvalidArgumentException("Customer Id hibás adattípus!");
			}
			$this->id = (integer)$id;
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCodeType(){

		return $this->codeType;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCodeValue(){
	
		return $this->codeValue;
	}
	
	/**
	 *
	 * @param string $codeValue
	 */
	public function setCodeValue($codeValue){
	
		$this->codeValue = substr($codeValue,0, 50);
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
	
	
}

?>