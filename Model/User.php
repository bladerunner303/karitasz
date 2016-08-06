<?php

require_once '../Util/Loader.php';

class User {
	
	
	private static function encodePassword($password){
		return hash('sha256', $password . '#' . Config::getContextParam("PASSWORD_SALT"));
	}
	
	private static function getCheckerTimeStamp(){
		return date('Y.m.d H:i:s', strtotime(date('Y-m-d H:i:s') . ' - 30 minute'));
	}
	
	private static function isForbidden($userId){
		
		$maxAttempt = (int)Config::getContextParam("THE_NUMBER_OF_ENTRY_ATTEMPTS");
		if ($maxAttempt < 1){
			Logger::warning("THE_NUMBER_OF_ENTRY_ATTEMPTS config nem található, vagy 1 nél kissebbre van beállítva. A rendszer így BRUTE FORCE támadásnak van kitéve! Kérlek állíts be egy 0-nál nagyobb pozítiv egész számot a web.xml állományban.");
			return false;
		}
		
		$db = Data::getInstance();
		$pre = $db->prepare("select count(*) cnt from bad_login 
							where user_id = :id 
							and status = 'AKTIV' 
							and created > :t");
		$params = array(
				':id' => $userId,
				':t' => self::getCheckerTimeStamp()
		);
		$pre->execute($params);
		$currentAttemptCount = $pre->fetch(PDO::FETCH_OBJ)->cnt;
		
		return ($maxAttempt <= $currentAttemptCount);
	}
	
	private static function addBadLogin($userId){
		$db = Data::getInstance();
		$pre = $db->prepare("insert into bad_login (id, user_id, created, status)
							 values (:id, :user_id, :created, 'AKTIV')");
		$params = array(
				':id' => SystemUtil::getGuid(),
				':user_id' => $userId,
				':created'=> SystemUtil::getCurrentTimestamp()
		);
		$pre->execute($params);
	}
	
	public static function login($userName, $userPassword){
		
		$ret = new LoginReturn();
		$user = self::get($userName);
		
		if ($user == null){
			//Nincs meg a user
			$ret->isGood = false;
			$ret->error = 'Hibás felhasználó vagy jelszó!';
			$ret->userId = null;
		}
		
		elseif (self::isForbidden($user->id)){
			$ret->isGood = false;
			$ret->error = 'A felhasználó átmenetileg kitiltott! Próbálkozz később';
			$ret->userId = null;
			Logger::info('Kitiltott felhasználóra próbálkozás. Felhasználó név: ' . $userName . ' Ip: ' . SystemUtil::getRequestIp());
		}
		
		elseif ((strlen($user->password) > 3) 
				&& ($user->password == self::encodePassword( $userPassword)) 
				&& ($user->status == 'AKTIV'))  {
		
			$ret->isGood = true;
			$ret->error = '';
			$ret->userId = $user->id;
			self::set($user, 'LOGIN');
		}
		else {
			//Ha itt járunk akkor szar a password
			$ret->isGood = false;
			$ret->error = 'Hibás felhasználó vagy jelszó!';
			$ret->userId = $user->id;
			self::addBadLogin($user->id);
		}
		
		return $ret;
				
	}
	
	public static function logout($userName){
		
		$user = self::get($userName);
		self::set($user, 'LOGOUT');		
		
	}
	
	public static function changePassword($oldPassword, $newPassword, $userName){
		
		if (strlen($newPassword) < 4) {
			throw new InvalidArgumentException('Az új jelszónak minimum 4 karakter hosszúnak kell lennie!');
		}
						
		$user = self::get($userName);
		if ((strlen($user->password) < 4) || ($user->password != self::encodePassword( $oldPassword))){
			throw new InvalidArgumentException('Nem megfelelő a régi jelszó!');
		}
				
		$user->password = self::encodePassword( $newPassword);	
		self::set($user, 'PASSWORD_CHANGE');		
		
	}
	
	public static function getAll(){
		
		$db = Data::getInstance();
		$pre = $db->prepare("select * from system_user order by lower(name)");
		$pre->execute();
		return $pre->fetchAll(PDO::FETCH_OBJ);
		/*
		$db = new Data();
		$db->setSql("select * from user order by lower(name)");
		return $db->query();	
		*/	
	}
	
	public static function get($userNameOrId){

		$db = Data::getInstance();
		$pre = $db->prepare("select * from system_user where name = :userNameOrId or id = :userNameOrId");
		$params = array(
				':userNameOrId' => $userNameOrId
		);
		$pre->execute($params);
		$array = $pre->fetchAll(PDO::FETCH_OBJ);
		if (count($array) == 0){
			return null;
		}
		else {
			return $array[0];
		}
				 		
	}
	
	// modified type possible value: 'USER_DATA', 'PASSWORD_CHANGE', 'LOGOUT', 'LOGIN'
	public static function set($user, $modifiedType = null){
		
		if ((empty($user->name))){
			throw new InvalidArgumentException('Nincs megadva felhasználó név!');				
		}
		
		if (($user->name != 'admin') && (!filter_var($user->email, FILTER_VALIDATE_EMAIL))){
			throw new InvalidArgumentException('Érvénytelen e-mail cím!');
		}
		
		$db = Data::getInstance();
		$pre = $db->prepare("select count(*) cnt from system_user where name = :name and id != coalesce(:id, '') ");
		$params = array(
				':name' => $user->name,
				':id' => $user->id
		);
		
		$pre->execute($params);
//		if ($pre->fetch(PDO::FETCH_NUM)[0] > 0) {
		if ($pre->fetch(PDO::FETCH_OBJ)->cnt > 0) {
			throw new InvalidArgumentException('Létezik már ez a felhasználó név:' . $user->name );
		}
		
		if (empty($user->id)){
		
			$pre = $db->prepare("insert into system_user (id, name, status, email, password, last_password_change, modifier, modified)
					values (:id, :name, :status, :email, :password, :last_password_change, :modifier, :modified)");
				
			$params = array(
					':id' => SystemUtil::getGuid(),
					':name' => substr(trim($user->name), 0, 35),
					':status' => $user->status,
					':email' => substr($user->email, 0, 255),
					':password' => self::encodePassword('password'),
					':last_password_change' => SystemUtil::getCurrentTimestamp(),
					':modifier' => $user->modifier,
					':modified' => SystemUtil::getCurrentTimestamp()
			);
				
			$pre->execute($params);
				
		}
		elseif (($modifiedType=='USER_DATA') || ($modifiedType == NULL)){
			
			$pre = $db->prepare("select * from system_user where id = :id");
			$params = array(
					':id' => $user->id
			);
			
			$pre->execute($params);
			$row = $pre->fetch(PDO::FETCH_OBJ);

			if (!$row){
				throw new Exception('Nem található az eredeti tétel');
			}
			else {
	
				if (
						($row->name != $user->name)
					||	($row->status != $user->status)
					||	($row->email != $user->email)
				){					

					$pre = $db->prepare("update system_user set
							name = :name,
							status = :status,
							email = :email,
							modifier = :modifier,
							modified = :modified
							where id = :id
							");
						
					$params = array(
							':id' => $user->id,
							':name' => substr(trim($user->name), 0, 35),
							':status' => $user->status,
							':email' => substr($user->email, 0, 255),
							':modifier' => $user->modifier,
							':modified' => SystemUtil::getCurrentTimestamp(),
					);
					$pre->execute($params);
					
				}
			}	
		}
		elseif ($modifiedType=='PASSWORD_CHANGE'){
			
			$pre = $db->prepare("update system_user set
					password = :password,
					last_password_change = :last_password_change
					where id = :id");
			$params = array(
					':password'=> $user->password,
					':last_password_change'=>SystemUtil::getCurrentTimestamp(),
					':id' => $user->id
			);
				
			$pre->execute($params);
			
		}
		elseif ($modifiedType == 'LOGOUT'){
			
			$pre = $db->prepare("update system_user set
					last_logout = :last_logout
					where id = :id");
			$params = array(
					':last_logout'=>SystemUtil::getCurrentTimestamp(),
					':id' => $user->id
			);
				
			$pre->execute($params);
			
		}
		elseif ($modifiedType == 'LOGIN'){
			$pre = $db->prepare("update system_user set
						last_login = :last_login
						where id = :id");
			$params = array(
					':last_login'=>SystemUtil::getCurrentTimestamp(),
					':id' => $user->id
			);			
			$pre->execute($params);
			
			$pre = $db->prepare("update bad_login set status = 'INAKTIV' where user_id = :id");
			$params = array(':id' => $user->id);			
			$pre->execute($params);
		}
					
	}
	
}

class LoginReturn {
	public $isGood;
	public $error;
	public $userId;
	
}

?>