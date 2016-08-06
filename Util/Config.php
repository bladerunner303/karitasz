<?php

// PHPmailer wrapper class
// Dependencie
// Logger
class Config {
	
	
	static function getDatabaseSettings(){
		$xml = self::readConfigFile();
		
		$databaseSetting = new DatabaseSetting();
		
		if ($xml != null){
			
			$acceptedTypes =  array('SQLITE', 'MYSQL');
			$databaseSetting->type = strtoupper ($xml->database->type);
			if (empty($databaseSetting->type)){
				Logger::error("Adatbázis szerver típusa nem került megadásra a config fájlban (database\type)");
			}						
			else if (!in_array($databaseSetting->type, $acceptedTypes )){
				Logger::error("Ismeretlen adatbázis típus!");
			}
			
			$databaseSetting->serverAddress = $xml->database->serverAddress;
			if (empty($databaseSetting->serverAddress) && ($databaseSetting->type != "SQLITE")){
				Logger::error("Adatbázis szerver címe nem került megadásra a config fájlban (database\serverAddress");
			}
				
			$databaseSetting->user = $xml->database->user;
			if (empty($databaseSetting->user) && ($databaseSetting->type != "SQLITE")){
				Logger::error("Adatbázis szerver User nem került megadásra a config fájlban (database\user)");
			}
				
			$databaseSetting->password = $xml->database->password;
			if (empty($databaseSetting->password) && ($databaseSetting->type != "SQLITE")){
				Logger::error("Adatbázis szerver Password nem került megadásra a config fájlban (database\password)");
			}
				
			$databaseSetting->port = ( int ) $xml->database->port;
			if (empty($databaseSetting->port)&& ($databaseSetting->type != "SQLITE")){
				Logger::error("Adatbázis szerver Port nem került megadásra a config fájlban");
			}			
				
			$databaseSetting->schema = $xml->database->schema;
			if (empty($databaseSetting->schema) && ($databaseSetting->type != "SQLITE")){
				Logger::error("Adatbázis schéma nem került megadásra a config fájlban");
			}
			
			$databaseSetting->path = $xml->database->path;
			if (empty($databaseSetting->path) && ($databaseSetting->type == "SQLITE")){
				Logger::error("Adatbázis elérési útvonal nem került megadásra a config fájlban");
			}
			
		}
		return $databaseSetting;
	}
	
	static function getContextParam($paramName){
		$xml = self::readConfigFile();
		
		$retVal = null;
		$contextParams = $xml->contextParam;
		foreach ($contextParams as $value) {
			if ($value->paramName == $paramName){
				$retVal = $value->paramValue;
			}
		}
		
		if (empty($retVal)){
			Logger::error("Nem található a $paramName context paraméter a config fáljban");
		}
		return $retVal;
	}
	
	static function getLogFileName(){
		$xml = self::readConfigFile();
	
		if ($xml != null){
			return 	$xml->logger->logFileName;
		}
		else {
			return 'alert';
		}
	}
	
	private static function readConfigFile(){
		if (file_exists ( '../Config/web.xml' )) {
			return simplexml_load_file ( '../Config/web.xml' );		
		} else {
			$error = 'Nem található a web.xml fájl!';
			Logger::error($error);
			return null;
		}
		
	}
	
	
	
}
?>