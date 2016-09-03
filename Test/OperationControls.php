<?php

define ( 'URL_LIST_OPERATION', 'listOperations.php');
define ( 'URL_LIST_OPERATION_DETAILS', 'listOperationDetails.php');
define ( 'URL_SAVE_OPERATION', 'saveOperation.php');
define ( 'OPERATION_COUNT', 2);

class OperationControls  extends UnitTestBase {
	
	function test_listOperations_good_simple_detail(){
		$response = $this->getResponse(URL_LIST_OPERATION . '?detail=GT_SZEKRENY&operation_type=FELAJANLAS', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
		
		$this->assertEqual($response->content[0]->customer_id, "F000027", "Nem jó a találat" . $response->content[0]->customer_id);
	}
	
	function test_listOperations_good_simple_customer_id(){
		$response = $this->getResponse(URL_LIST_OPERATION . '?operation_type=KERVENYEZES&customer_id=K000221', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->customer_id, "K000221", "Nem jó a találat" . $response->content[0]->customer_id);
	}
	
	function test_listOperations_good_simple_id(){
		$response = $this->getResponse(URL_LIST_OPERATION . '?id=-1000', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->customer_id, "F000027", "Nem jó a találat: " . $response->content[0]->customer_id);
		$this->assertEqual($response->content[0]->status, "FOLYAMATBAN", "Nem jó a találat: " . $response->content[0]->status);
		$this->assertEqual(count($response->content[0]->operationDetails), 2, "Nem jó a találat: " . count($response->content[0]->operationDetails));
	}

	function test_listOperations_good_simple_text(){
		$response = $this->getResponse(URL_LIST_OPERATION . '?operation_type=FELAJANLAS&text=-1000', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		if (count($response->content) != 1){
			$this->fail('Nem 1 találatot kaptunk vissza, hanem ' . count($response->content));
			return;
		}
	
		$this->assertEqual($response->content[0]->customer_id, "F000027", "Nem jó a találat: " . $response->content[0]->customer_id);
		$this->assertEqual($response->content[0]->status, "FOLYAMATBAN", "Nem jó a találat: " . $response->content[0]->status);
	}
	
	function test_listOperations_bad_cookie(){
		$this->checkBadCookie(URL_LIST_OPERATION);
	}
	
	function test_saveOperation_good_simple_new(){
		$operation = $this::getOperationObject();
		
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from operation where id = (select max(id) from operation where id not in (-1000, -1005, -1010))")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Nem insertált sort');
			return;
		}
		
		$this->assertEqual($row->operation_type, $operation->operation_type, "operation_type mező nem egyezik " . $row->operation_type);
		$this->assertEqual($row->has_transport, $operation->has_transport, "has_transport mező nem egyezik " . $row->has_transport);
		$this->assertEqual($row->is_wait_callback, $operation->is_wait_callback, "is_wait_callback mező nem egyezik " . $row->is_wait_callback);
		$this->assertEqual($row->customer_id, $operation->customer_id, "customer_id mező nem egyezik " . $row->customer_id);
		$this->assertEqual($row->status, $operation->status, "status mező nem egyezik " . $row->status);
		
		$detailRows = $db->query("select * from operation_detail where operation_id = " . $row->id . " order by order_indicator")->fetchAll(PDO::FETCH_OBJ);
		if (count($detailRows) != 2) {
			$this->fail('Nem insertálta be az összes details sort:' . count($detailRows));
		}
		
		foreach ($detailRows as $key => $detail) {
	
			$this->assertEqual($detail->name, $operation->operationDetails[$key]->name, "name mező nem egyezik " . $detail->name);
			$this->assertEqual($detail->goods_type, $operation->operationDetails[$key]->goods_type, "goods_type mező nem egyezik " . $detail->goods_type);
			$this->assertEqual($detail->status, $operation->operationDetails[$key]->status, "is_wait_callback mező nem egyezik " . $detail->status);
			
		}
		
	}
	
	function test_saveOperation_good_simple_modify(){
		$this->test_saveOperation_good_simple_new();
		$db = Data::getInstance($this->unitDbSetting);
		$operation = $db->query("select * from operation where id = (select max(id) from operation where id not in (-1000, -1005, -1010))")->fetch(PDO::FETCH_OBJ);
		$operation->operationDetails = $db->query("select * from operation_detail where operation_id = " . $operation->id . " order by name")->fetchAll(PDO::FETCH_OBJ);
		
		$operation->status = "FOLYAMATBAN";
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$row = $db->query("select * from operation where id = '" . $operation->id . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Eltűnt az eredeti sor');
			return;
		}
		$this->assertEqual($row->operation_type, $operation->operation_type, "operation_type mező nem egyezik " . $row->operation_type);
		$this->assertEqual($row->has_transport, $operation->has_transport, "has_transport mező nem egyezik " . $row->has_transport);
		$this->assertEqual($row->is_wait_callback, $operation->is_wait_callback, "is_wait_callback mező nem egyezik " . $row->is_wait_callback);
		$this->assertEqual($row->customer_id, $operation->customer_id, "customer_id mező nem egyezik " . $row->customer_id);
		$this->assertEqual($row->status, $operation->status, "status mező nem egyezik " . $row->status);
		
		$operation->operationDetails[2] = new stdClass();
		$operation->operationDetails[2]->name = 'Babaágy pelenkázóval';
		$operation->operationDetails[2]->goods_type = 'GT_BABA_AGY';
		$operation->operationDetails[2]->status = 'ROGZITETT';
		
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$detailRows = $db->query("select * from operation_detail where operation_id = " . $row->id . " order by order_indicator")->fetchAll(PDO::FETCH_OBJ);
		if (count($detailRows) != 3) {
			$this->fail('Nem insertálta be az összes details sort:' . count($detailRows));
		}
		
		foreach ($detailRows as $key => $detail) {
	
			$this->assertEqual($detail->name, $operation->operationDetails[$key]->name, "name mező nem egyezik " . $detail->name);
			$this->assertEqual($detail->goods_type, $operation->operationDetails[$key]->goods_type, "goods_type mező nem egyezik " . $detail->goods_type);
			$this->assertEqual($detail->status, $operation->operationDetails[$key]->status, "is_wait_callback mező nem egyezik " . $detail->status);
			
		}
	}
	
	function test_saveOperation_not_modify(){
		$this->test_saveOperation_good_simple_new();
		$db = Data::getInstance($this->unitDbSetting);
		$operation = $db->query("select * from operation where id = (select max(id) from operation where id not in (-1000, -1005, -1010))")->fetch(PDO::FETCH_OBJ);
		$operation->operationDetails = $db->query("select * from operation_detail where operation_id = " . $operation->id . " order by name")->fetchAll(PDO::FETCH_OBJ);
		
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		
		$row = $db->query("select * from operation where id = '" . $operation->id . "'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Eltűnt az eredeti sor');
			return;
		}
		$this->assertEqual($row->modified, $operation->modified, "modified mező nem egyezik " . $row->modified . " vs " . $operation->modified);
		$this->assertEqual($row->modifier, $operation->modifier, "modifier mező nem egyezik " . $row->modifier);
		$this->assertEqual($row->created, $operation->created, "created mező nem egyezik " . $row->created);
		$this->assertEqual($row->creator, $operation->creator, "creator mező nem egyezik " . $row->creator);
		$this->assertEqual($row->operation_type, $operation->operation_type, "operation_type mező nem egyezik " . $row->operation_type);
		$this->assertEqual($row->has_transport, $operation->has_transport, "has_transport mező nem egyezik " . $row->has_transport);
		$this->assertEqual($row->is_wait_callback, $operation->is_wait_callback, "is_wait_callback mező nem egyezik " . $row->is_wait_callback);
		$this->assertEqual($row->customer_id, $operation->customer_id, "customer_id mező nem egyezik " . $row->customer_id);
		$this->assertEqual($row->status, $operation->status, "status mező nem egyezik " . $row->status);
		
		$detailRows = $db->query("select * from operation_detail where operation_id = " . $row->id . " order by order_indicator")->fetchAll(PDO::FETCH_OBJ);
		if (count($detailRows) != 2) {
			$this->fail('Nem insertálta be az összes details sort:' . count($detailRows));
		}
		
		foreach ($detailRows as $key => $detail) {
		
			$this->assertEqual($detail->name, $operation->operationDetails[$key]->name, "name mező nem egyezik " . $detail->name);
			$this->assertEqual($detail->goods_type, $operation->operationDetails[$key]->goods_type, "goods_type mező nem egyezik " . $detail->goods_type);
			$this->assertEqual($detail->status, $operation->operationDetails[$key]->status, "is_wait_callback mező nem egyezik " . $detail->status);
				
		}
	}

	function test_saveOperation_bad_cookie(){
		$this->checkBadCookie(URL_SAVE_OPERATION);
	}
	
	function test_saveOperation_bad_customer_qualification(){
		$operation = $this::getOperationObject();
		$operation->customer_id = 'K000246';
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Tiltott státuszú ügyfél részére kérvény vagy felajánlás nem rögzíthető!", "Nem megfelelő a hibaüzenet" . $response->content);
		$this->checkNoRowInsert();
		
	}
	
	function test_saveOperation_bad_customer_type(){
		$operation = $this::getOperationObject();
		$operation->operation_type = 'FELAJANLAS';
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Kérvényező ügyfél csak kérvényt adhat be!", "Nem megfelelő a hibaüzenet" . $response->content);
		
		$operation->operation_type = 'KERVENYEZES';
		$operation->customer_id = 'F000027';
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Felajánló ügyfél csak felajánlást adhat be!", "Nem megfelelő a hibaüzenet" . $response->content);
		
		$this->checkNoRowInsert();
	}
	
	function test_saveOperation_has_another_request(){
		$db = Data::getInstance($this->unitDbSetting);
		$db->exec ("update operation set status = 'FOLYAMATBAN' where id = '-1010'");
		$operation =  $this::getOperationObject();
		$response = $this->getResponse(URL_SAVE_OPERATION, $this->getPhpSessionCookie(), json_encode($operation));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Az ügyfélnek már van másik folyamatban lévő kérvénye! Kérlek módostsd inkább azt!", "Nem megfelelő a hibaüzenet" . $response->content);
		$this->checkNoRowInsert();
	}
	
	private function checkNoRowInsert(){
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from operation where id = (select max(id) from operation where id not in (-1000, -1005, -1010))")->fetch(PDO::FETCH_OBJ);
		if ($row){
			$this->fail('Insertált sort');
			return;
		}
	}
	
	private static function getOperationObject(){
		$operation = new stdClass();
		$operation->operation_type = 'KERVENYEZES';
		$operation->has_transport = 'Y';
		$operation->is_wait_callback = 'N';
		$operation->customer_id = 'K000221';
		$operation->status = 'ROGZITETT';
		$operation->operationDetails = array();
		$operation->operationDetails[0] = new stdClass();
		$operation->operationDetails[0]->name = 'A Ruhás szekrény';
		$operation->operationDetails[0]->goods_type = 'GT_SZEKRENY';
		$operation->operationDetails[0]->status = 'ROGZITETT';
		$operation->operationDetails[1] = new stdClass();
		$operation->operationDetails[1]->name = 'Nagy komod';
		$operation->operationDetails[1]->goods_type = 'GT_KOMOD';
		$operation->operationDetails[1]->status = 'ROGZITETT';
		
		return $operation;	
	}
	
}
?>