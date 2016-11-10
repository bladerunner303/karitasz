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
		$transport->addresses[0]->order_indicator = 0;
		$transport->addresses[1] = new stdClass();
		$transport->addresses[1]->operation_id = '-1005';
		$transport->addresses[1]->zip= '1081';
		$transport->addresses[1]->city= 'Budapest';
		$transport->addresses[1]->street= 'Szitás u. 113/a';
		$transport->addresses[1]->status= 'ROGZITETT';		
		$transport->addresses[1]->description = '9-es kapucsöngö';
		$transport->addresses[1]->order_indicator = 1;
		return $transport;
	}

}
?>