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
	
	public function save(){
		if ($this->code_type  != 'goods_type'){
			throw new InvalidArgumentException('Nem engedélyezett kód típus mentése! Csak goods_type típus engedélyezett!');
		}
		
		if ((mb_strlen($this->code_value) < 2) || (mb_strlen($this->code_value) > 18)){
			throw new InvalidArgumentException('Nem megfelelő hosszúságú kód! Csak 2 és 18 karakter közötti engedélyezett!' );
		}
		
		//Szerkessze át a kulcs mezőt
		$id = 'GT_' . str_replace(" ", "_", mb_strtoupper($this->code_value));
		$id = str_replace("Á", "A", $id);
		$id = str_replace("Ä", "A", $id);
		$id = str_replace("É", "E", $id);
		$id = str_replace("Í", "I", $id);
		$id = str_replace("Ő", "O", $id);
		$id = str_replace("Ö", "O", $id);
		$id = str_replace("Ó", "O", $id);
		$id = str_replace("Ú", "U", $id);
		$id = str_replace("Ű", "U", $id);
		$id = str_replace("Ü", "U", $id);
		$this->id = $id;
		
		$db = Data::getInstance();
		
		//szöveg nem találahtó még a rögzítettek között
		$pre = $db->prepare("select count(*) cnt from code where id=:id");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		if ((int)$pre->fetch(PDO::FETCH_OBJ)->cnt == 1){
			throw new InvalidArgumentException('Már létezik ez a kód');
		}
		
		//Törölje a nem hozzárendelt goods_type-oakt
		$db = Data::getInstance();
		$db->exec("delete from code  where code_type = 'goods_type' and id not in (select goods_type from operation_detail) ");
		
		//Insertáljon
		$t = SystemUtil::getCurrentTimestamp();
		$sql = "insert into code (id, code_type, code_value, modifier, modified) values (:id, :code_type, :code_value, :modifier, :modified)";
		$pre = $db->prepare($sql);
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':code_type', $this->code_type, PDO::PARAM_STR);
		$pre->bindValue(':code_value', $this->code_value, PDO::PARAM_STR);
		$pre->bindValue(':modifier', $this->modifier, PDO::PARAM_STR);
		$pre->bindValue(':modified', $t, PDO::PARAM_STR);
		
		$pre->execute();
		
		return $this->id;
	}
	
	private $id;
	private $code_type;
	private $code_value ;
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
			$this->id = $id;
		}
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getCodeType(){

		return $this->code_type;
	}
	
	public function setCodeType($codeType){
		
		$this->code_type = substr(trim($codeType),0, 35);
	}
	
	/**
	 *
	 * @return string
	 */
	public function getCodeValue(){
	
		return $this->code_value;
	}
	
	/**
	 *
	 * @param string $codeValue
	 */
	public function setCodeValue($codeValue){
	
		
		$this->code_value = substr(trim($codeValue),0, 50);
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