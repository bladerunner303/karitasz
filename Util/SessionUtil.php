<?php

class SessionUtil {
	
	public static function logPageLoad($page){
		Session::logPageLoad($page, isset($_COOKIE['sessionId'])? $_COOKIE['sessionId'] : null);
	}
	
	public static function clear(){
		$sessionId = isset($_COOKIE['sessionId'])? $_COOKIE['sessionId'] : null;
		if ($sessionId != null){
			$userInfo = Session::getUserInfo($sessionId);
			User::logout($userInfo->userName);
			Session::close($sessionId);
			setcookie("sessionId", "", time()-3600);
		}
	}
	
	public static function validSession(){
		$sessionId = isset($_COOKIE['sessionId'])? $_COOKIE['sessionId'] : null;

		if ($sessionId == null){
			return false;
		}
		
		return Session::isValid($sessionId);
	}
	
	public static function validRole($validRoles){
		$sessionId = isset($_COOKIE['sessionId'])? $_COOKIE['sessionId'] : null;
		if ($sessionId == null){
			return false;
		}
		
		foreach ($validRoles as $role) {
		
			$userInfo = Session::getUserInfo($sessionId);
			if (strpos($userInfo->userRoles, $role) !== false){
				return true;
			}
		}
		return  false;
	}
	
	
}

?>