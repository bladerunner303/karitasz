<?php

define ( 'URL_LIST_CUSTOMER', 'listCustomers.php');
define ( 'URL_LIST_CUSTOMER_HISTORY', 'listCustomerHistory.php');
define ( 'URL_SIMILAR_CUSTOMER', 'similarCustomers.php');
define ( 'URL_SAVE_CUSTOMER', 'saveCustomer.php');
define ( 'FELAJANLO_COUNT', 2);
define ( 'SIMILAR_COUNT', 4);
define ( 'HISTORY_COUNT', 2);

class CustomerControls  extends UnitTestBase {

	function test_listCustomers_good_simple_customer_type(){
		$response = $this->getResponse(URL_LIST_CUSTOMER . '?customer_type=FELAJANLO', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != FELAJANLO_COUNT){
			$this->fail('Nem '. FELAJANLO_COUNT . ' találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
		
		$this->assertEqual($response->content[0]->surname, "Aszabó Menyhértné", "Nem jó a találat" . $response->content[0]->surname);
	}
	
	function test_listCustomers_good_simple_text(){
		$response = $this->getResponse(URL_LIST_CUSTOMER . '?text=317654321', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->full_name, "Kovács Marianna", "Nem jó a találat" . $response->content[0]->surname);
	}
	
	function test_listCustomers_good_simple_id(){
		$response = $this->getResponse(URL_LIST_CUSTOMER . '?id=F000005', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->full_name, "Cég Group", "Nem jó a találat: " . $response->content[0]->full_name);
	}
	
	function test_listCustomers_bad_cookie(){
		$this->checkBadCookie(URL_LIST_CUSTOMER);
	}
	
	
	
	function test_similarCustomer_good_simple(){
		$response = $this->getResponse(URL_SIMILAR_CUSTOMER . '?tax_number=12345678&id=F000005&phone=201234567&surname=Kovács&zip=1121&street=Fürj u. 42', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));

		if (count($response->content) != SIMILAR_COUNT){
			$this->fail('Nem '. SIMILAR_COUNT . ' találatot kaptunk vissza, hanem ' . count($response->content));
			echo (json_encode($response->content));
			return;
		}
		
		$this->assertEqual($response->content[0]->id, "K000621", "Nem jó a találat az 1 sorban" . $response->content[0]->id);
		$this->assertEqual($response->content[1]->id, "K000221", "Nem jó a találat az 2 sorban" . $response->content[1]->id);
		$this->assertEqual($response->content[2]->id, "K000246", "Nem jó a találat a 3 sorban" . $response->content[2]->id);
		$this->assertEqual($response->content[3]->id, "F000027", "Nem jó a találat a 4 sorban" . $response->content[3]->id);
	}
	
	function test_similarCustomer_bad_cookie(){
		$this->checkBadCookie(URL_SIMILAR_CUSTOMER);
	}
	
	function test_saveCustomer_good_simple_new(){
		$customer = $this::getCustomerObject();
		
		$response = $this->getResponse(URL_SAVE_CUSTOMER, $this->getPhpSessionCookie(), json_encode($customer));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from customer where surname = '" . $customer->surname . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Nem insertált sort');
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
	}
	
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
	
}
?>