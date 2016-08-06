<?php
/*
 * verzio: 1.0
 * release date: 2016.03.03
 */
class SystemUtil {
	public static function getGuid(){
		mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45);// "-"
		//$uuid = chr(123)// "{"
		$uuid = ''
				.substr($charid, 0, 8).$hyphen
				.substr($charid, 8, 4).$hyphen
				.substr($charid,12, 4).$hyphen
				.substr($charid,16, 4).$hyphen
				.substr($charid,20,12)
				//.chr(125)// "}"
		;
		return $uuid;
	}
	
	public static function getCurrentTimestamp(){
		return date("Y-m-d H:i:s");
	}
	
	public static function getRequestIp(){
		return
		!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] :
		!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] :$_SERVER['REMOTE_ADDR'];
	}
	
	public static function getRequestBrowserHash(){
		return hash('md5', EMPTY($_SERVER['HTTP_USER_AGENT'])? 'N/A' : $_SERVER['HTTP_USER_AGENT']);
	}
	
	public static function cast($destination, $sourceObject)
	{
		if (is_string($destination)) {
			$destination = new $destination();
		}
		$sourceReflection = new ReflectionObject($sourceObject);
		$destinationReflection = new ReflectionObject($destination);
	
		$sourceProperties = $sourceReflection->getProperties();
		$classMethods = get_class_methods($destination);
	
		foreach ($sourceProperties as $sourceProperty) {
			$sourceProperty->setAccessible(true);
			$name = $sourceProperty->getName();
			$value = $sourceProperty->getValue($sourceObject);
				
			$setterFunction = "set";
			$names = explode("_", $name);
			foreach ($names as $nameItem) {
				$setterFunction .= ucfirst($nameItem);
			}
				
			if (in_array($setterFunction, $classMethods) ){
				$destination->$setterFunction($value); //Ez egy paraméteres settert feltételez.
				//		Logger::info('talált settert ' . $setterFunction);
			}
			
			else if ($destinationReflection->hasProperty($name)) {
				$propDest = $destinationReflection->getProperty($name);
				$propDest->setAccessible(true);
				$propDest->setValue($destination,$value);
			//		Logger::info('nem talált settert ' . $setterFunction);
			} else {
				$destination->$name = $value;
			//		Logger::info('nem talált settert: ' . $setterFunction);
			}
			
			}
			return $destination;
	}
	
	
	
}
?>