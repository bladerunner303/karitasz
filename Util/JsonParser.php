<?php

class JsonParser {
	public static function getJson($myObject) {
		return self::getJsonUnescapedUnicode ( json_encode ( $myObject ) );
	}
	private static function getJsonUnescapedUnicode($unicodeString) {
		$unicodeString = str_replace ( '\u00c1', 'Á', $unicodeString );
		$unicodeString = str_replace ( '\u00e1', 'á', $unicodeString );
		$unicodeString = str_replace ( '\u00c4', 'Ä', $unicodeString );
		$unicodeString = str_replace ( '\u00e4', 'ä', $unicodeString );
		$unicodeString = str_replace ( '\u00c9', 'É', $unicodeString );
		$unicodeString = str_replace ( '\u00e9', 'é', $unicodeString );
		$unicodeString = str_replace ( '\u00cd', 'Í', $unicodeString );
		$unicodeString = str_replace ( '\u00ed', 'í', $unicodeString );
		$unicodeString = str_replace ( '\u00d3', 'Ó', $unicodeString );
		$unicodeString = str_replace ( '\u00f3', 'ó', $unicodeString );
		$unicodeString = str_replace ( '\u00d6', 'Ö', $unicodeString );
		$unicodeString = str_replace ( '\u00f6', 'ö', $unicodeString );
		$unicodeString = str_replace ( '\u0150', 'Ő', $unicodeString );
		$unicodeString = str_replace ( '\u0151', 'ő', $unicodeString );
		$unicodeString = str_replace ( '\u00da', 'Ú', $unicodeString );
		$unicodeString = str_replace ( '\u00fa', 'ú', $unicodeString );
		$unicodeString = str_replace ( '\u00dc', 'Ü', $unicodeString );
		$unicodeString = str_replace ( '\u00fc', 'ü', $unicodeString );
		$unicodeString = str_replace ( '\u0170', 'Ű', $unicodeString );
		$unicodeString = str_replace ( '\u0171', 'ű', $unicodeString );
	
		return $unicodeString;
	}
	
	public static function sendJson($myObject){
		header ( 'Content-type: application/json; charset=UTF-8' );
		if (function_exists('http_response_code')){
			http_response_code(200);
		}
		else {
			header("HTTP/1.1 200 OK");
		}
		echo self::getJson ( $myObject );
	}
	
	public static function sendError($code, $message){
		
		header ( 'Content-type: application/json; charset=UTF-8' );
		header("HTTP/1.1 $code  Internal Server Error");
		echo $message;
		
	}
	
	public static function sendSessionExpired(){
		header ( 'Content-type: application/json; charset=UTF-8' );
		header("HTTP/1.1 401 Unauthorized");
		echo "Lejárt, vagy nem található session!";
	}
	
	public static function sendRoleError(){
		header ( 'Content-type: application/json; charset=UTF-8' );
		header("HTTP/1.1 403 Forbidden");
		echo "Nincs jogosultsága a funkcióra!";
	}
	
	public static function sendMethodError(){
		header ( 'Content-type: application/json; charset=UTF-8' );
		header("HTTP/1.1 405 Method Not Allowed");
		echo "Hibás hívás!";
	}
}
?>