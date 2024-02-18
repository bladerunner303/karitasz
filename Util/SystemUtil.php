<?php
/*
 * verzio: 1.0
 * release date: 2016.03.03
 */
class SystemUtil {
	public static function getGuid(){
		$data = random_bytes(16);

    // Az 1-4. bájtok verziót határozzák meg
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

    // A karakterek közötti kötőjeleket adja hozzá
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}

	public static function getCurrentTimestamp(){
		return date("Y-m-d H:i:s");
	}

	public static function getCurrantDay() {
		return date("Y-m-d");
	}

	public static function getRequestIp(){
		$ip = $_SERVER['REMOTE_ADDR'];
		if (array_key_exists('HTTP_X_FORWARDED_FOR', $_SERVER)) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} elseif (array_key_exists('HTTP_CLIENT_IP', $_SERVER)) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		return $ip;
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
