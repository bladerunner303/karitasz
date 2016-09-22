<?php

require_once('../Util/Data.php');
require_once('../Util/DatabaseSetting.php');
require_once('simpletest/autorun.php');

define ( 'APP_NAME', 'unit_test' );

class UnitTestBase extends UnitTestCase {
	
	private $currentSessionId = '';
	public $unitDbSetting = '';
	
	function testCreation(){
	
	}
	
	
	function setUp() {
		
		//session_set_cookie_params(0);
		/*
		$_SESSION['userName'] = 'admin';
		$_SESSION['userId'] = 'e26cb7f0-57e2-4eb7-8094-67ec97f349be';
		$_SESSION['LAST_ACTIVITY'] = time();
		*/
	//	$this->currentSessionId = '80924c0c-4380-4707-9631-844c57aa73e8';
		$this->currentSessionId = SystemUtil::getGuid();
		
		
		$this->unitDbSetting = new DatabaseSetting();
		$this->unitDbSetting->type = "MYSQL";
		$this->unitDbSetting->serverAddress = "localhost";
		$this->unitDbSetting->schema = "karitasz";
		$this->unitDbSetting->user = "root";
		$this->unitDbSetting->password = "root";
	
		$db = Data::getInstance($this->unitDbSetting);
		
		$db->exec("insert into session 
					(id,ip, browser_hash, user_name, user_id, login_time, last_activity)
					values
					('$this->currentSessionId' ,'127.0.0.1', '382b0f5185773fa0f67a8ed8056c7759',
						'levi', 'a', '" . date('Y.m.d H:i:s') . "', '" . date('Y.m.d H:i:s') . "')");
		
		$db->exec("delete from transport_address where id<>'0'");
		$db->exec("delete from transport where id <> '0'");
		$db->exec("delete from code where id in ('GT_ALMAFA', 'GT_BABA_AGY', 'GT_SZEKRENY') ");
		$db->exec("delete from operation_detail where id <> '0'");
		$db->exec("delete from operation where id <> 0");
		$db->exec("delete from customer_family_member where id <> '0'");
		$db->exec("delete from customer_history where id <> '0'");
		$db->exec("delete from customer where id <> '0'");

		$db->exec("insert into code values ('GT_BABA_AGY', 'goods_type', 'Baba ágy', 'SYSTEM', current_timestamp)");
		$db->exec("insert into code values ('GT_SZEKRENY', 'goods_type', 'Szekrény', 'SYSTEM', current_timestamp)");
		
		$db->exec("INSERT INTO customer(id, surname, forename, customer_type, zip, city, street, phone, qualification, description, additional_contact, additional_contact_phone, status, creator, created, modifier, modified) 
				VALUES ('K000221','Dudás','Ildikó','KERVENYEZO','1111','Budapest','Baross u. 28.' ,'201234567','NORMAL',NULL,NULL,NULL,'AKTIV','SYSTEM',CURRENT_TIMESTAMP,'SYSTEM',CURRENT_TIMESTAMP);");
		$db->exec("INSERT INTO customer(id, surname, forename, customer_type, zip, city, street, phone, qualification, description, additional_contact, additional_contact_phone, status, creator, created, modifier, modified) 
				VALUES ('K000246','Kovács','Marianna','KERVENYEZO','1081','Budapest','Szitás u. 113/a','317654321','TILTOTT','Eladja a kapott butorokat',NULL,NULL,'AKTIV','SYSTEM',CURRENT_TIMESTAMP,'SYSTEM',CURRENT_TIMESTAMP);");
		$db->exec("INSERT INTO customer(id, surname, forename, customer_type, zip, city, street, phone, qualification, description, additional_contact, additional_contact_phone, status, creator, created, modifier, modified) 
				VALUES ('F000027','Aszabó Menyhértné',null,'FELAJANLO',1121,'Budapest','Fürj u. 42.','11234567','NORMAL',NULL,NULL,NULL,'AKTIV','SYSTEM',CURRENT_TIMESTAMP,'SYSTEM',CURRENT_TIMESTAMP);");
		$db->exec("INSERT INTO customer(id, surname, forename, customer_type, zip, city, street, phone, qualification, description, additional_contact, additional_contact_phone, status, creator, created, modifier, modified) 
				VALUES ('F000005','Cég Group',NULL,'FELAJANLO',1111,'Budapest','Szemere u. 87.','307654321','NORMAL', NULL, 'Hornok Edina','306701825','AKTIV','SYSTEM',CURRENT_TIMESTAMP,'SYSTEM',CURRENT_TIMESTAMP);");
		$db->exec("INSERT INTO customer(id, surname, forename, customer_type, zip, city, street, phone, qualification, description, additional_contact, additional_contact_phone, status, tax_number, creator, created, modifier, modified)
				VALUES ('K000621','Kiss','Blanka','KERVENYEZO','2100','Gödöllő','Fő u. 28.' ,'701234567','NORMAL',NULL,NULL,NULL,'AKTIV','12345678', 'SYSTEM',CURRENT_TIMESTAMP,'SYSTEM',CURRENT_TIMESTAMP);");
		
		$db->exec("INSERT INTO customer_history(id, customer_id, old_value, new_value, data_type, created, creator)
				VALUES ('a', 'F000005','Régi név', 'Cég Group', 'NAME_CHANGE', CURRENT_TIMESTAMP, 'SYSTEM');");
		$db->exec("INSERT INTO customer_history(id, customer_id, old_value, new_value, data_type, created, creator)
				VALUES ('B', 'F000005','307654326', '307654321', 'PHONE_CHANGE' , CURRENT_TIMESTAMP, 'SYSTEM');");

		$db->exec("insert into customer_family_member(id, customer_id, name, family_member_type)
					values ('h77c8204-5ti3-99eb-11e6-9013f7c56u7d', 'F000005', 'Családtag Csaba', 'FM_GYERMEK')");
		
		$db->exec("insert into customer_family_member(id, customer_id, name, family_member_type, birth_date)
				values ('c8204hj4-8t93-9rec-11f6-9f73e7u5fegb', 'K000221', 'Feleség Fanni', 'FM_HAZASTARS', '1984-10-10')");
		
		$db->exec("INSERT INTO operation 
				(id, operation_type, has_transport, is_wait_callback, customer_id, status, description, neediness_level, sender, 
				income_type, income, others_income, creator, created, modifier, modified, last_status_changed, last_status_changed_user) 
				VALUES (-1000, 'FELAJANLAS', 'Y', 'Y', 'F000027', 'FOLYAMATBAN', NULL, NULL, NULL, 
				NULL, NULL, NULL, 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, 'SYSTEM')");
		$db->exec("INSERT INTO operation
				(id, operation_type, has_transport, is_wait_callback, customer_id, status, description, neediness_level, sender,
				income_type, income, others_income, creator, created, modifier, modified, last_status_changed, last_status_changed_user)
				VALUES (-1005, 'KERVENYEZES', 'N', 'Y', 'K000246', 'BEFEJEZETT', NULL, NULL, NULL,
				NULL, NULL, NULL, 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP,  CURRENT_TIMESTAMP, 'SYSTEM')");
		$db->exec("INSERT INTO operation
				(id, operation_type, has_transport, is_wait_callback, customer_id, status, description, neediness_level, sender,
				income_type, income, others_income, creator, created, modifier, modified, last_status_changed, last_status_changed_user)
				VALUES (-1010, 'KERVENYEZES', 'Y', 'N', 'K000221', 'BEFEJEZETT', NULL, NULL, NULL,
				NULL, NULL, NULL, 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP,  CURRENT_TIMESTAMP, 'SYSTEM')");
		
		$db->exec("INSERT INTO `operation_detail` (`id`, `operation_id`, `name`, `goods_type`, `storehouse_id`, `status`, `order_indicator`) 
				VALUES ('c5af3004-5a23-11e6-99eb-0013f7cf157c', '-1000', 'Kis gyermek ágy pelenkázóval', 'GT_BABA_AGY', NULL, 'ROGZITETT', '1');");
		$db->exec("INSERT INTO `operation_detail` (`id`, `operation_id`, `name`, `goods_type`, `storehouse_id`, `status`, `order_indicator`)
				VALUES ('b28b2833-0d3b-43ac-80e7-5ff6452ed873', '-1000', 'Ruhás szekrény', 'GT_SZEKRENY', NULL, 'ROGZITETT', '2');");
		
		$db->exec("INSERT INTO transport (id, transport_date, status, creator, created, modifier, modified) 
				   VALUES ('caTf36E4-7a23-14e6-99Cf-0a13f7cd65tl', '2016-09-21', 'KIADOTT', 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP)");

		$db->exec("INSERT INTO transport (id, transport_date, status, creator, created, modifier, modified)
				VALUES ('brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ', '2016-09-15', 'BEFELYEZETT', 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP)");
		
		$db->exec("INSERT INTO transport (id, transport_date, status, creator, created, modifier, modified)
				VALUES ('G8Tf36E4-7a23-14e6-Z9Cf-0a13f7cd35Fl', '2016-09-28', 'ROGZITETT', 'SYSTEM', CURRENT_TIMESTAMP, 'SYSTEM', CURRENT_TIMESTAMP)");
		
		$db->exec("INSERT INTO transport_address (id, operation_id, transport_id, zip, city, street, description)
				VALUES ('40be00cd-7325-4659-9518-b5afc88e4e5a', '-1010', 'brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ', 1111, 'Budapest', 'Pitypang u 3', null )");
		$db->exec("INSERT INTO transport_address (id, operation_id, transport_id, zip, city, street, description)
				VALUES ('970d746a-2c3e-4df6-866d-9c58d0688df2', '-1005', 'brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ', 1121, 'Budapest', 'Seholsincs tér 3', null )");
		$db->exec("INSERT INTO transport_address (id, operation_id, transport_id, zip, city, street, description)
				VALUES ('fc1c0333-d64a-4f06-8e15-b507881d0773', '-1010', 'caTf36E4-7a23-14e6-99Cf-0a13f7cd65tl', 1131, 'Budapest', 'Imott-amott köz 45', null )");
		
	}
	
	function tearDown() { }
		
	function getResponse($url, $cookies = array(),$postParameters = array()){
		if (!is_array($cookies)){
			$cookies =  array($cookies);
		}
		
		$cookiesString = '';
		foreach ($cookies as $cookie) {
			$cookiesString = $cookiesString . $cookie . ';';
		}
		
		$method = 'GET';
		$postdata = '';
		if (!is_array($postParameters)){
			$postdata = $postParameters;
			$method = 'POST';
				
		}
		else {
			$postdata = json_encode($postParameters);
			$method = 'GET';
		}

		$opts = array('http' =>
				array(
						'protocol_version' => 1.1,
						'ignore_errors' => true,
						'method'  => $method,
						'header'  => 'Cookie: ' . $cookiesString . '\r\nContent-type: application/json; charset=UTF-8;',
						'content' => $postdata
				)
		);
		
		session_write_close();
		$content =  @file_get_contents(SERVICE_ROOT . $url, false, stream_context_create($opts));

		$header = explode(" ", $http_response_header[0]);
		
		$ret = new stdClass();
		$ret->code = $header[1];
		if ($ret->code == 200){	
			$ret->content = json_decode($content);
		}
		else {
			$ret->content = $content;
		}
		$ret->error = $header[2];
		return $ret;
		
	}
	
	function getPhpSessionCookie(){
		
		return 'sessionId=' . $this->currentSessionId;
	}
	
	function checkBadCookie($url, $post = array()){
		$response = $this->getResponse($url, "NINCSILYEN", $post);
		$this->assertEqual(401, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual("Lejárt, vagy nem található session!", $response->content, "Nem megfelelő a hibaüzi" . $response->code);
	}
	
	
}

class PostParameter{
	
	public $key;
	public $value;
	
}

?>