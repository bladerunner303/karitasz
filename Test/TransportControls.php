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
		
		$this->assertEqual($response->content[0]->id, "-1005", "Nem jó a találat" . $response->content[0]->id);
	}
	
	function test_listTransport_good_simple_date(){
		$response = $this->getResponse(URL_LIST_TRANSPORT . '?begin_date=2016-09-20&end_date=2016-09-27' , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->id, "-1000", "Nem jó a találat" . $response->content[0]->id);
	}

	function test_listTransport_good_simple_id(){
		$response = $this->getResponse(URL_LIST_TRANSPORT . '?id=-1005' , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->id, "-1005", "Nem jó a találat" . $response->content[0]->id);
		$this->assertEqual(count($response->content[0]->addresses), 2, "Nem jó a találat: " . count($response->content[0]->addresses));
	}
	
	function test_listTransport_good_simple_text(){
		$response = $this->getResponse(URL_LIST_TRANSPORT . '?text=Dudás' , $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 2){
			$this->fail('Nem 2 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->id, "-1005", "Nem jó a találat" . $response->content[0]->id);
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
		$transport->addresses[0]->status = "BEFEJEZETT_TRANSPORT";
		$transport->status = "KIADOTT_TRANSPORT";
		
		$response = $this->getResponse(URL_SAVE_TRANSPORT, $this->getPhpSessionCookie(), json_encode($transport));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$row = $db->query("select * from transport where transport_date = '" . $transport->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Eletünt az eredeti sor');
			return;
		}
		$this->assertEqual($row->status, "KIADOTT_TRANSPORT", "status mező nem egyezik " . $row->status);
		
		$addresses = $db->query("select * from transport_address where transport_id='" . $row->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		
		if (count($addresses) != 2){
			var_dump($addresses);
			$this->fail('Nem insertált elég sort hanem ' . count($addresses));
			return;
		}	
		
		$row = $addresses[0];
		$this->assertEqual($row->zip, $transport->addresses[0]->zip, "zip mező nem egyezik " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[0]->city, "city mező nem egyezik " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[0]->street, "street mező nem egyezik " . $row->street);
		$this->assertEqual($row->status, "BEFEJEZETT_TRANSPORT", "status mező nem egyezik " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[0]->description, "description mező nem egyezik " . $row->description);
		
		$row = $addresses[1];
		$this->assertEqual($row->zip, $transport->addresses[1]->zip, "zip mező nem egyezik 2-es sorban " . $row->zip);
		$this->assertEqual($row->city, $transport->addresses[1]->city, "city mező nem egyezik 2-es sorban " . $row->city);
		$this->assertEqual($row->street, $transport->addresses[1]->street, "street mező nem egyezik 2-es sorban " . $row->street);
		$this->assertEqual($row->status, $transport->addresses[1]->status, "status mező nem egyezik 2-es sorban " . $row->status);
		$this->assertEqual($row->description, $transport->addresses[1]->description, "description mező nem egyezik 2-es sorban " . $row->description);
		
		$cnt = (int)$db->query("select count(*) cnt from transport_address_item where transport_address_id = '" . $transport->addresses[0]->id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 2, "Nem megfelelő számú address itemet hozot létre, hanem: " .  $cnt);
		
	}
	
	function test_saveTransport_good_simple_modify_item_all_success(){
		$this->test_saveTransport_good_simple_modify();
		
		$db = Data::getInstance($this->unitDbSetting);
		$transport = $db->query("select * from transport where transport_date = '" . $this::getTransportObject()->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		$transport->addresses = $db->query("select * from transport_address where transport_id='" . $transport->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		$transport->addresses[0]->items = $db->query("select * from transport_address_item where transport_address_id='" . $transport->addresses[0]->id . "'")->fetchAll(PDO::FETCH_OBJ);
		
		for ($i = 0; $i < count($transport->addresses[0]->items); $i++) {
			$transport->addresses[0]->items[$i]->status = 'BEFEJEZETT_TRANSPORT';
		}
		
		$response = $this->getResponse(URL_SAVE_TRANSPORT, $this->getPhpSessionCookie(), json_encode($transport));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$cnt = (int)$db->query("select count(*) cnt from transport_address_item 
								where status = 'BEFEJEZETT_TRANSPORT' and transport_address_id = '" . $transport->addresses[0]->id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 2, "Nem megfelelő számú address itemet módosított, hanem: " .  $cnt);
		
		$cnt = (int)$db->query("select count(*) cnt from operation_detail 
				where status = 'BEFEJEZETT' and id = '" . $transport->addresses[0]->items[0]->operation_detail_id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 1, "Nem állította befejezettre az operation_detail státuszát");
		
		$cnt = (int)$db->query("select count(*) cnt from operation
				where status = 'BEFEJEZETT' and id = '" . $transport->addresses[0]->operation_id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 1, "Nem állította befejezettre az operation státuszát");
		
	}

	function test_saveTransport_good_simple_modify_item_not_all_success(){
		$this->test_saveTransport_good_simple_modify();
	
		$db = Data::getInstance($this->unitDbSetting);
		$transport = $db->query("select * from transport where transport_date = '" . $this::getTransportObject()->transport_date . "'")->fetch(PDO::FETCH_OBJ);
		$transport->addresses = $db->query("select * from transport_address where transport_id='" . $transport->id . "' order by operation_id desc")->fetchAll(PDO::FETCH_OBJ);
		$transport->addresses[0]->items = $db->query("select * from transport_address_item where transport_address_id='" . $transport->addresses[0]->id . "'")->fetchAll(PDO::FETCH_OBJ);
	
		$transport->addresses[0]->items[0]->status = 'BEFEJEZETT_TRANSPORT';
	
		$response = $this->getResponse(URL_SAVE_TRANSPORT, $this->getPhpSessionCookie(), json_encode($transport));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
	
		$cnt = (int)$db->query("select count(*) cnt from transport_address_item
				where status = 'BEFEJEZETT_TRANSPORT' and transport_address_id = '" . $transport->addresses[0]->id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 1, "Nem megfelelő számú address itemet módosított, hanem: " .  $cnt);
	
		$cnt = (int)$db->query("select count(*) cnt from operation_detail
				where status = 'BEFEJEZETT' and id = '" . $transport->addresses[0]->items[0]->operation_detail_id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 1, "Nem állította befejezettre az operation_detail státuszát");
		
		$cnt = (int)$db->query("select count(*) cnt from operation_detail
				where status = 'BEFEJEZETT' and id = '" . $transport->addresses[0]->items[1]->operation_detail_id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 0, "Átállította befejezettre az operation_detail státuszát");
	
		$cnt = (int)$db->query("select count(*) cnt from operation
				where status = 'BEFEJEZETT' and id = '" . $transport->addresses[0]->operation_id .  "'")->fetch(PDO::FETCH_OBJ)->cnt;
		$this->assertEqual($cnt, 0, "Átállította befejezettre az operation státuszát");
	
	}
	
	function test_saveTransport_bad_cookie(){
		$this->checkBadCookie(URL_SAVE_TRANSPORT);
	}
	
	private static function getTransportObject(){
		
		$transport = new stdClass();
		$transport->transport_date = '2016-09-27';
		$transport->status = 'ROGZITETT_TRANSPORT';
		$transport->addresses = array();
		$transport->addresses[0] = new stdClass();
		$transport->addresses[0]->operation_id = '-1000';
		$transport->addresses[0]->zip= '1121';
		$transport->addresses[0]->city= 'Budapest';
		$transport->addresses[0]->street= 'Fürj u. 42.';
		$transport->addresses[0]->status= 'ROGZITETT_TRANSPORT';
		$transport->addresses[0]->description = '';
		$transport->addresses[0]->order_indicator = 0;
		$transport->addresses[0]->transport_id = null;
		$transport->addresses[0]->id = null;
		$transport->addresses[1] = new stdClass();
		$transport->addresses[1]->operation_id = '-1005';
		$transport->addresses[1]->zip= '1081';
		$transport->addresses[1]->city= 'Budapest';
		$transport->addresses[1]->street= 'Szitás u. 113/a';
		$transport->addresses[1]->status= 'ROGZITETT_TRANSPORT';		
		$transport->addresses[1]->description = '9-es kapucsöngö';
		$transport->addresses[1]->order_indicator = 1;
		$transport->addresses[1]->transport_id = null;
		$transport->addresses[1]->id = null;
		return $transport;
	}

}
?>