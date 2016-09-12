<?php

define ( 'URL_LIST_TRANSPORT', 'listTransports.php');
define ( 'URL_SAVE_TRANSPORT', 'saveTransport.php');
define ( 'TRANSPORT_COUNT', 3);

class TransportControls  extends UnitTestBase {

	function test_listTransport_good_simple(){
		$response = $this->getResponse(URL_LIST_TRANSPORT , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != TRANSPORT_COUNT){
			$this->fail('Nem '. TRANSPORT_COUNT . ' találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
		
		$this->assertEqual($response->content[0]->id, "brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ", "Nem jó a találat" . $response->content[0]->id);
	}
	
	function test_listTransport_good_simple_date(){
		$response = $this->getResponse(URL_LIST_TRANSPORT . '?begin_date=2016-09-20&end_date=2016-09-27' , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->id, "caTf36E4-7a23-14e6-99Cf-0a13f7cd65tl", "Nem jó a találat" . $response->content[0]->id);
	}

	function test_listTransport_good_simple_id(){
		$response = $this->getResponse(URL_LIST_TRANSPORT . '?id=brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ' , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->id, "brTf36E4-7a23-14e6-9RCf-9a1WfFRd55tZ", "Nem jó a találat" . $response->content[0]->id);
		$this->assertEqual(count($response->content[0]->addresses), 2, "Nem jó a találat: " . count($response->content[0]->addresses));
	}
	
	function test_listTransport_bad_cookie(){
		$this->checkBadCookie(URL_LIST_TRANSPORT);
	}
	

	function test_saveTransport_good_simple_new(){
		$transport = $this::getTransportObject();
		
		$response = $this->getResponse(URL_SAVE_TRANSPORT, $this->getPhpSessionCookie(), json_encode($transport));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from transport where transport_date = '" . $transport->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Nem insertált sort');
			return;
		}
		$this->assertEqual($row->status, $transport->status, "status mező nem egyezik " . $row->status);
	
		$addresses = $db->query("select * from transport_address where transport_id='" . $row->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		
		if (count($addresses) != 2){
			$this->fail('Nem insertált elég sort');
			return;
		}
		$row = $addresses[0];
		$this->assertEqual($row->zip, $transport->addresses[0]->zip, "zip mező nem egyezik " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[0]->city, "city mező nem egyezik " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[0]->street, "street mező nem egyezik " . $row->street);
		$this->assertEqual($row->status, $transport->addresses[0]->status, "status mező nem egyezik " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[0]->description, "description mező nem egyezik " . $row->description);
		
		$row = $addresses[1];
		$this->assertEqual($row->zip, $transport->addresses[1]->zip, "zip mező nem egyezik 2-es sorban " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[1]->city, "city mező nem egyezik 2-es sorban " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[1]->street, "street mező nem egyezik 2-es sorban " . $row->street);
		$this->assertEqual($row->status, $transport->addresses[1]->status, "status mező nem egyezik 2-es sorban " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[1]->description, "description mező nem egyezik 2-es sorban " . $row->description);
		
	}
	
	function test_saveTransport_good_simple_modify(){
		$this->test_saveTransport_good_simple_new();
		$db = Data::getInstance($this->unitDbSetting);
		$transport = $db->query("select * from transport where transport_date = '" . $this::getTransportObject()->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		$transport->addresses = $db->query("select * from transport_address where transport_id='" . $transport->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		$transport->addresses[0]->status = "BEFEJEZETT";
		$transport->status = "FOLYAMATBAN";
		
		$response = $this->getResponse(URL_SAVE_TRANSPORT, $this->getPhpSessionCookie(), json_encode($transport));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$row = $db->query("select * from transport where transport_date = '" . $transport->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Eletünt az eredeti sor');
			return;
		}
		$this->assertEqual($row->status, "FOLYAMATBAN", "status mező nem egyezik " . $row->status);
		
		$addresses = $db->query("select * from transport_address where transport_id='" . $row->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		
		if (count($addresses) != 2){
			$this->fail('Nem insertált elég sort');
			return;
		}	
		
		$row = $addresses[0];
		$this->assertEqual($row->zip, $transport->addresses[0]->zip, "zip mező nem egyezik " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[0]->city, "city mező nem egyezik " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[0]->street, "street mező nem egyezik " . $row->street);
		$this->assertEqual($row->status, "BEFEJEZETT", "status mező nem egyezik " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[0]->description, "description mező nem egyezik " . $row->description);
		
		$row = $addresses[1];
		$this->assertEqual($row->zip, $transport->addresses[1]->zip, "zip mező nem egyezik 2-es sorban " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[1]->city, "city mező nem egyezik 2-es sorban " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[1]->street, "street mező nem egyezik 2-es sorban " . $row->street);
		$this->assertEqual($row->status, $transport->addresses[1]->status, "status mező nem egyezik 2-es sorban " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[1]->description, "description mező nem egyezik 2-es sorban " . $row->description);
		
		
	}
	
	function test_saveTransport_bad_cookie(){
		$this->checkBadCookie(URL_SAVE_TRANSPORT);
	}
	
	private static function getTransportObject(){
		
		$transport = new stdClass();
		$transport->transport_date = '2016-09-27';
		$transport->status = 'ROGZITETT';
		$transport->addresses = array();
		$transport->addresses[0] = new stdClass();
		$transport->addresses[0]->operation_id = '-1000';
		$transport->addresses[0]->zip= '1121';
		$transport->addresses[0]->city= 'Budapest';
		$transport->addresses[0]->street= 'Fürj u. 42.';
		$transport->addresses[0]->status= 'ROGZITETT';
		$transport->addresses[0]->description = '';
		$transport->addresses[1] = new stdClass();
		$transport->addresses[1]->operation_id = '-1005';
		$transport->addresses[1]->zip= '1081';
		$transport->addresses[1]->city= 'Budapest';
		$transport->addresses[1]->street= 'Szitás u. 113/a';
		$transport->addresses[1]->status= 'ROGZITETT';		
		$transport->addresses[1]->description = '9-es kapucsöngö';
		return $transport;
	}
	/*
	
	function test_saveCustomer_good_simple_modify(){
		$this->test_saveCustomer_good_simple_new();
		$db = Data::getInstance($this->unitDbSetting);
		$customer = $db->query("select * from customer where surname = '" . $this::getCustomerObject()->surname . "'")->fetch(PDO::FETCH_OBJ);
		$customer->status = 'INAKTIV';
		$customer->surname = 'Teszt Tamara';
		$customer->street = 'Második utca 3';
		$customer->phone = '311234567';
		$customer->additional_contact = 'Kata';
		$customer->tb_number = '7654321';
		
		$response = $this->getResponse(URL_SAVE_CUSTOMER, $this->getPhpSessionCookie(), json_encode($customer));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
	
		$row = $db->query("select * from customer where surname = '" . $customer->surname . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Eltűnt az eredeti sor');
			return;
		}
		$this->assertEqual($row->surname, $customer->surname, "surname mező nem egyezik " . $row->surname);
		$this->assertEqual($row->forename, $customer->forename, "forename mező nem egyezik " . $row->forename);
		$this->assertEqual($row->zip, $customer->zip, "zip mező nem egyezik " . $row->zip);
		$this->assertEqual($row->city, $customer->city, "city mező nem egyezik " . $row->city);
		$this->assertEqual($row->street, $customer->street, "street mező nem egyezik " . $row->street);
		$this->assertEqual($row->customer_type, $customer->customer_type, "customer_type mező nem egyezik " . $row->customer_type);
		$this->assertEqual($row->phone, $customer->phone, "phone mező nem egyezik " . $row->phone);
		$this->assertEqual($row->qualification, $customer->qualification, "qualification mező nem egyezik " . $row->qualification);
		$this->assertEqual($row->description, $customer->description, "description mező nem egyezik " . $row->description);
		$this->assertEqual($row->status, $customer->status, "status mező nem egyezik " . $row->status);
		$this->assertEqual($row->tax_number, $customer->tax_number, "tax number mező nem egyezik " . $row->tax_number);
		$this->assertEqual($row->tb_number, $customer->tb_number, "tb number mező nem egyezik " . $row->tb_number);
		$this->assertEqual($row->additional_contact, $customer->additional_contact, "additional_contact mező nem egyezik " . $row->additional_contact);
	
		$histories = $db->query("select * from customer_history where customer_id = '" . $row->id . "' order by case when data_type='NAME_CHANGE' THEN 1 ELSE 2 end ")->fetchAll(PDO::FETCH_OBJ);
		
		if (count($histories) != 6) {
			$this->fail('Nem vett fel elég history sort!');
			return;
		}
		$nameHistory = $histories[0];
		$this->assertEqual($nameHistory->old_value, $this::getCustomerObject()->surname . ' ' . $this::getCustomerObject()->forename, "Nem egyezik a név régi érték:" . $nameHistory->old_value);
		$this->assertEqual($nameHistory->new_value, $customer->surname . ' ' . $customer->forename, "Nem egyezik a név új érték:" . $nameHistory->new_value);
		$this->assertEqual($nameHistory->created, $row->modified , "Nem egyezik a létrehozási idő");
		$this->assertEqual($nameHistory->creator, $row->modifier , "Nem egyezik a létrehozó");
		$this->assertEqual($nameHistory->data_type, 'NAME_CHANGE' , "Nem egyezik a data_type: " . $nameHistory->data_type);
		
	}
	
	function test_saveCustomer_bad_cookie(){
		$this->checkBadCookie(URL_SAVE_CUSTOMER);
	}
	
	private static function getCustomerObject(){
		$customer = new stdClass();
		$customer->surname = 'Családnév';
		$customer->forename = 'Keresztnév';
		$customer->zip = '1111';
		$customer->city = 'Budapest';
		$customer->street = 'Teszt Tibor Tér 3';
		$customer->customer_type = 'KERVENYEZO';
		$customer->phone = '301234567';
		$customer->qualification = 'NORMAL';
		$customer->description = 'Árvíztűrő Tükörfúrógép';
		$customer->additional_contact = null;
		$customer->additional_contact_phone = null;
		$customer->status = 'AKTIV'	;
		$customer->tax_number = '12345678';
		$customer->tb_number = null;
		$customer->birth_place = null;
		$customer->birth_date = null;
		return $customer;	
	}
	
	function test_listCustomerHistory_good_simple_id(){
		$response = $this->getResponse(URL_LIST_CUSTOMER_HISTORY . '?id=F000005', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != HISTORY_COUNT){
			$this->fail('Nem ' . HISTORY_COUNT . ' találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->data_type, "PHONE_CHANGE", "Nem jó a data_type: " . $response->content[0]->data_type);
		$this->assertEqual($response->content[0]->data_type_local, "Telefonszám változás", "Nem jó a data_type_local: " . $response->content[0]->data_type_local);		
		$this->assertEqual($response->content[1]->new_value, "Cég Group", "Nem jó az új érték: " . $response->content[0]->new_value);
		$this->assertEqual($response->content[1]->old_value, "Régi név", "Nem jó a régi érték: " . $response->content[0]->old_value);

	}
	
	function test_listCustomerHistory_bad_cookie(){
		$this->checkBadCookie(URL_LIST_CUSTOMER_HISTORY);
	}
	*/
}
?>