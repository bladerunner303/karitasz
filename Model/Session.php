<?php

require_once '../Util/Loader.php';

class Session {
	
	private static $userInfo = null;
	
	public static function logPageLoad($page, $sessionId){
		if (empty($sessionId) || (empty($page))){
			Logger::warning('logPageLoad nem kapott megfelelő paramétereket: $sessionId:' . $sessionId . ' $page: ' . $page);
			return;
		}
		
		$db = Data::getInstance();
		
		$pre = $db->prepare("select user_id, user_name, ip from session where id = :id" );
		$params = array(
				':id' => $sessionId ,
		);
		$pre->execute($params);
		$sessionData = $pre->fetch(PDO::FETCH_OBJ);
		Logger::warning(json_encode($sessionData));
		$userAgent = $_SERVER['HTTP_USER_AGENT'];
		
		$pre = $db->prepare( "insert into log_page_load (id, page, page_load_time, user_id, user_name, ip, user_agent)
				values (:id, :page, :page_load_time, :user_id, :user_name, :ip, :user_agent)");
		$logId = SystemUtil::getGuid();
		$t = SystemUtil::getCurrentTimestamp();
		$params = array(
				':id' => $logId ,
				':page' => $page, 
				':page_load_time' => $t,
				':user_id' => $sessionData->user_id,
				':user_name' => $sessionData->user_name,
				':ip' => $sessionData->ip,
				':user_agent' => $userAgent
		);
		
		$pre->execute($params);
		
	}
	
	public static function open ($userId, $userName){
	
		$db = Data::getInstance();

		$pre = $db->prepare( "insert into session (id, user_id, user_name, ip, browser_hash, last_activity, login_time)
				values (:id, :user_id, :user_name, :ip_address, :browser_hash, :last_activity, :login_time)");
		$sessionId = SystemUtil::getGuid();
		
		$t = SystemUtil::getCurrentTimestamp();
		$params = array(
				':id' => $sessionId ,
				':user_id' => $userId,
				':user_name' => $userName,
				':ip_address' => SystemUtil::getRequestIp(), 
				':browser_hash' => SystemUtil::getRequestBrowserHash(),
				':last_activity' => $t,
				':login_time' => $t
		);
		
		$pre->execute($params);
		self::clearSessions();
		
		return $sessionId;
	}
	
	public static function isValid($sessionId){
		
		$db = Data::getInstance();
		$pre = $db->prepare( "select count(*) as cnt from session 
								where id =:id 
								and ip = :ip
								and browser_hash = :browser_hash
								and last_activity > :last_activity");
		$params = array(
				':id' => $sessionId ,
				':ip' => SystemUtil::getRequestIp(),
				':browser_hash' => SystemUtil::getRequestBrowserHash(),
				':last_activity' => self::getCheckerTimeStamp()
		);
		//Logger::info( SystemUtil::getRequestBrowserHash() . " " . SystemUtil::getRequestIp());
		$pre->execute($params);
		$sessionCount = $pre->fetch()[0];

		if ($sessionCount == 1){
			$pre = $db->prepare( "update session set last_activity = :last_activity where id = :id");
			$params = array(
				':id' => $sessionId ,
				':last_activity' => SystemUtil::getCurrentTimestamp()
			);
			$pre->execute($params);
			return true;
		}
		else {
			self::close($sessionId);
			return false;
		}
	}
	
	public static function getUserInfo($sessionId){

		if (self::$userInfo == null){
			$db = Data::getInstance();
			$pre = $db->prepare( "select 
					user_id , 
					user_name  
				from 
					session 
				where 
					id = :id");
			$params = array(
				':id' => $sessionId 
			);
			$pre->execute($params);

			if ($row = $pre->fetch(PDO::FETCH_OBJ)){
				$userInfo = new stdClass();
				$userInfo->userId = $row->user_id;
				$userInfo->userName = $row->user_name;
				self::$userInfo = $userInfo;
			}
			else {
				$userInfo = new stdClass();
				$userInfo->userId = '';
				$userInfo->userName = '';
				self::$userInfo = $userInfo;
			}
		}
		return self::$userInfo;
	}
	
	public static function close($sessionId){
		$db = Data::getInstance();
		$pre = $db->prepare( "delete from session where id = :id");
		$params = array(
				':id' => $sessionId 
			);
		$pre->execute($params);
		
		self::clearSessions();
	}
	
	private static function clearSessions(){
		//Nem  hagyjuk 1000 főlé nöni a sessionok számát
		
		//Először a lejárt, de ki nem léptetett sessionoket löjük le
		$db = Data::getInstance();
		
		$pre = $db->prepare("delete from session
				where id in (select * from (select id from session where last_activity < :checker_timestamp) x)");
		
		$params = array(
				':checker_timestamp' => self::getCheckerTimeStamp()
		);
		$pre->execute($params);
		
		//Ezután az akár aktív sessionoket is lelöjjük
		$db->exec("delete from session
				where id not in (select * from (select id from session order by last_activity desc limit 1000) x)");
	}
	
	private static function getCheckerTimeStamp(){
		return date('Y.m.d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 minute'));
	}
}

?>