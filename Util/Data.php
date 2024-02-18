<?php

require_once 'Loader.php';

class Data extends PDO
{

	private static $_dbType;
	public static function getDbType(){
		if (empty(self::$_dbType)){
			self::$_dbType = Config::getDatabaseSettings()->type ;
		}
		return self::$_dbType;
	}

	public static function getEngineSpecificSql($sql){

		if (self::getDbType() == 'POSTGRESQL'){

			$params = array();
			$beginParams = explode(':', $sql);

			foreach ($beginParams as $key => $beginParam) {
				if ($key != 0) {
					$param = ":" . str_replace(")", "", substr($beginParam, 0, stripos($beginParam, " ")) );
					array_push($params, $param);

				}
			}

			foreach ($params as $param) {
				$sql = str_replace($param . " is null", $param . "::text is null", $sql);
			}
		}
		elseif (self::getDbType() == 'SQLITE'){
			$params = array();
			$beginParams = explode(':', $sql);

			foreach ($beginParams as $key => $beginParam) {
				if ($key != 0) {
					$param = ":" . str_replace(")", "", substr($beginParam, 0, stripos($beginParam, " ")) );
					array_push($params, $param);

				}
			}

			foreach ($params as $param) {
				$sql = str_replace($param . " is null", "coalesce(" . $param . ", '') = ''", $sql);
			}
		}
		return $sql;
	}

	private static function getDsn(DatabaseSetting $databaseSettings){
		switch ($databaseSettings->type) {
			case "SQLITE":
				//return "sqlite:c:/path/database.sqlite";
				return "sqlite:" . $databaseSettings->path;
				break;
			case "MYSQL":
				return "mysql:host=" . $databaseSettings->serverAddress . ";dbname=" .  $databaseSettings->schema;
				break;
			case "POSTGRESQL":
				return "pgsql:host=" . $databaseSettings->serverAddress . ";dbname=" .  $databaseSettings->schema;
				break;
			default:
				throw new Exception("Ismeretlen, vagy nem kiolvasható adatbázis motor!");
				break;
		}
	}

	private static $dataInstance = NULL;

	public static function getInstance (DatabaseSetting $databaseSetting = NULL){
		if (self::$dataInstance == NULL){
			if ($databaseSetting == null){
				$databaseSetting = Config::getDatabaseSettings();
			}
			$dsn = self::getDsn($databaseSetting);
			self::$dataInstance = new Data($dsn, $databaseSetting->user, $databaseSetting->password);
			self::$dataInstance->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
			self::$dataInstance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			self::$dataInstance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

			if (Config::getDatabaseSettings()->type == "MYSQL"){
				self::$dataInstance->exec("set names utf8");
				self::$dataInstance->exec('SET SQL_SAFE_UPDATES=0');
			}
			if (Config::getDatabaseSettings()->type == "SQLITE"){
				self::$dataInstance->setAttribute(PDO::ATTR_TIMEOUT, 0);
			}
		}
		return self::$dataInstance;
	}

	// Magic method clone is empty to prevent duplication of connection
	private function __clone() {
		throw new Exception("Nem clonozható a Singleton objektum!");
	}

	public function prepare($statement, $driver_options=NULL): PDOStatement|false{
		if ($driver_options == NULL){
			return parent::prepare(self::getEngineSpecificSql($statement));
		}
		else {
			return parent::prepare(self::getEngineSpecificSql($statement), $driver_options);
		}
	}

	/*
	public function beginTransaction(){
		if (self::getDbType() == 'SQLITE'){
			if (self::exec("BEGIN EXCLUSIVE TRANSACTION") === 0){
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return parent::beginTransaction();
		}
	}

	public function commit(){
		if (self::getDbType() == 'SQLITE'){
			if (self::exec("COMMIT TRANSACTION") === 0){
				return true;
			}
			else {
				return false;
			}
		}
		else {
			return parent::commit();
		}
	}

	public function rollBack(){
		if (self::getDbType() == 'SQLITE'){
			if (self::exec("ROLLBACK TRANSACTION") === 0){
				return true;
			}
			else {

				return false;
			}
		}
		else {
			return parent::rollBack();
		}
	}
	*/
	/**
	 * @param $paramValue paraméter értéke
	 * @param int $paramPDOType paraméter PDO típusa
	 * @return number Végleges paraméter PDO típus
	 */
	public static function getParameterType($paramValue, $paramPDOType){
		return empty($paramValue) ? PDO::PARAM_NULL : $paramPDOType;
	}

}

?>
