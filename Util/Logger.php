<?php

class Logger {
	static function error($message){
		self::writeLog("[ERROR] " . $message);
	}
	static function info($message){
		self::writeLog("[INFO] " . $message);
	}
	static function warning($message){
		self::writeLog("[WARNING] " . $message);
	}
	
	private static function writeLog($message){
		
		$fp = fopen ( "../Log/" . Config::getLogFileName() . ".log", "a" );
		fputs ( $fp, date ( 'Y-m-d H:i:s' ) . $message . "\r\n" );
		fclose ( $fp );
	}
}
?>