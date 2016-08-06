<?php

class Loader{
	static function modelClassLoader($classname){	
		$classFile = "../Model/". $classname .".php";
		self::includeFile($classFile);
	}
		
	static function utilClassLoader($classname){
		$classFile =   "../Util/". $classname .".php";
		self::includeFile($classFile);
	}
	
	static function utilHtml2Pdf($classname){
		$classFile = "../Util/html2pdf/" . strtolower($classname) . ".class.php";
		self::includeFile($classFile);
	}
	
	private static function includeFile($classFile){
		if (is_readable($classFile)){
			include $classFile;
		}
	}
}
		
	spl_autoload_register("Loader::modelClassLoader");
	spl_autoload_register("Loader::utilClassLoader");
	spl_autoload_register("Loader::utilHtml2Pdf");

?>