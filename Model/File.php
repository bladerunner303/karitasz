<?php

require_once '../Util/Loader.php';

class File implements JsonSerializable {
	
	public function jsonSerialize() {
		return get_object_vars($this);	
	}
	
	/**
	 * @return array<File>
	 */
	public function find(){
		
		$sql = "select 
					fm.*,
					fc.content
				from 
					file_meta_data fm,
					file_content fc
				where fm.file_content_id = fc.id
				and fm.id = :id ";
				
		$db = Data::getInstance();
		$pre = $db->prepare($sql);
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);	
		$pre->execute();
		$ret =  $pre->fetchAll(PDO::FETCH_OBJ);
		
		$sql = "update file_meta_data set last_downloaded = :t where id = :id";
		$pre = $db->prepare($sql);
		$t = SystemUtil::getCurrentTimestamp();
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':t', $t, PDO::PARAM_STR);
		$pre->execute();
		return $ret;
	}
	
	/**
	 * @return string
	 */
	public function save(){

		$t = SystemUtil::getCurrentTimestamp();
		$db = Data::getInstance();
		
		$this->id = SystemUtil::getGuid();
		$this->file_content_id = SystemUtil::getGuid();
		$t = SystemUtil::getCurrentTimestamp();
		
		$pre = $db->prepare ("insert into file_content (id, content) values (:id, :content)");
		$pre->bindValue(':id', $this->file_content_id, PDO::PARAM_STR);
		$pre->bindValue(':content', $this->content, PDO::PARAM_STR);
		$pre->execute();
		
		$pre = $db->prepare ("insert into file_meta_data (id, file_content_id, name, extension, size, creator, created, last_downloaded) 
								values (:id, :file_content_id, :name, :extension, :size, :creator, :created, null)");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->bindValue(':file_content_id', $this->file_content_id, PDO::PARAM_STR);
		$pre->bindValue(':name', $this->name, PDO::PARAM_STR);
		$pre->bindValue(':extension', $this->extension, PDO::PARAM_STR);
		$pre->bindValue(':size', $this->size, PDO::PARAM_STR);
		$pre->bindValue(':creator', $this->creator, PDO::PARAM_STR);
		$pre->bindValue(':created', $t, PDO::PARAM_STR);
		$pre->execute();
		
		return $this->id;

	}
	
	public function remove(){
		$ret = $this->find();
		if (count($ret) == 0){
			return;
		}
		$db = Data::getInstance();
		$pre = $db->prepare ( "select file_content_id from file_meta_data where id = :id");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		$this->file_content_id =  $pre->fetch(PDO::FETCH_OBJ)->file_content_id;
		
		$pre = $db->prepare ( "delete from operation_file where file_meta_data_id = :id");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		
		$pre = $db->prepare ( "delete from file_meta_data where id = :id");
		$pre->bindValue(':id', $this->id, PDO::PARAM_STR);
		$pre->execute();
		
		$pre = $db->prepare ( "delete from file_content where id = :file_content_id");
		$pre->bindValue(':file_content_id', $this->file_content_id, PDO::PARAM_STR);
		$pre->execute();
		
	}
	
	
	private $id;
	private $file_content_id;
	private $name;
	private $extension;
	private $size;
	private $last_downloaded;
	private $creator;
	private $created;
	private $content;
	
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
	public function getFileContentId(){
	
		return $this->file_content_id;
	}
	
	/**
	 *
	 * @param string $fileContentId
	 */
	public function setFileContentId($fileContentId){
	
		$this->file_content_id = $fileContentId;
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
	
		$this->name = empty($name)? null : substr($name, 0, 105);
		return $this;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getExtension(){
	
		return $this->extension;
	}
	
	/**
	 *
	 * @param string $extension
	 */
	public function setExtension($extension){
	
		$this->extension = empty($extension)? null : substr($extension, 0, 10);
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getSize(){
	
		return $this->size;
	}
	
	/**
	 *
	 * @param string $size
	 */
	public function setSize($size){
	
		$this->size = $size;
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

		$this->creator = substr($creator,0, 20);
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
	public function getLastDownloaded(){

		return $this->last_downloaded;
	}
	
	/**
	 *
	 * @return string
	 */
	public function getContent(){
	
		return $this->content;
	}
	
	/**
	 *
	 * @param string $content
	 */
	public function setContent($content){
	
		$this->content = $content;
		return $this;
	}
		
}

?>