<?php

define ('URL_LIST_CODES', 'listCodes.php');
define ('URL_SAVE_CODE', 'saveCode.php');


class CodeControls  extends UnitTestBase {

	function test_listCodes_good_simple(){
		$customer_type_count = 2;
		$operation_status_count = 3;
		
		$response = $this->getResponse( URL_LIST_CODES . '?codeTypes=customer_type;operation_status', $this->getPhpSessionCookie());
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . json_encode($response->content));
		
		if (count($response->content->customer_type) != $customer_type_count){
			$this->fail("Nem $customer_type_count  customer_type találatot kaptunk vissza, hanem " . count($response->content->$customer_type));
			return;
		}

		if (count($response->content->operation_status) != $operation_status_count){
			$this->fail("Nem $operation_status_count  $operation_status találatot kaptunk vissza, hanem " . count($response->content->$operation_status));
			return;
		}
		$this->assertEqual($response->content->customer_type[0]->id, "FELAJANLO", "Nem jó a találat " . $response->content->customer_type[0]->id);
		$this->assertEqual($response->content->customer_type[0]->code_value, "Felajánló", "Nem jó a találat " . $response->content->customer_type[0]->code_value);
		$this->assertEqual($response->content->operation_status[0]->id, "BEFEJEZETT", "Nem jó a találat " . $response->content->operation_status[0]->id);
		$this->assertEqual($response->content->operation_status[0]->code_value, "Befejezett", "Nem jó a találat " . $response->content->operation_status[0]->code_value);
	
	}
	
	function test_listCodes_bad_cookie(){
		$this->checkBadCookie(URL_LIST_CODES);
	}
	
	function test_saveCode_good_simple(){
		$code = $this->getCodeObject();
		
		$response = $this->getResponse(URL_SAVE_CODE, $this->getPhpSessionCookie(), json_encode($code));
		$this->assertEqual(200, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
	
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from code where id = 'GT_KORTEFA'")->fetch(PDO::FETCH_OBJ);
		if (!$row){
			$this->fail('Nem insertált sort');
			return;
		}
		
	}
	
	function test_saveCode_bad_code_type(){
		$code = $this->getCodeObject();
		$code->code_type = 'barmi';
		$response = $this->getResponse(URL_SAVE_CODE, $this->getPhpSessionCookie(), json_encode($code));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Nem engedélyezett kód típus mentése! Csak goods_type típus engedélyezett!", "Nem megfelelő a hibaüzenet" . $response->content);
		$this->checkNoRowInsert();
		
	}
	
	function test_saveCode_bad_code_value(){
		$code = $this->getCodeObject();
		$code->code_value = 'b';
		$response = $this->getResponse(URL_SAVE_CODE, $this->getPhpSessionCookie(), json_encode($code));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Nem megfelelő hosszúságú kód! Csak 2 és 18 karakter közötti engedélyezett!", "Nem megfelelő a hibaüzenet" . $response->content);
		$this->checkNoRowInsert();
	
		$code->code_value = '123456789a123456789';
		$response = $this->getResponse(URL_SAVE_CODE, $this->getPhpSessionCookie(), json_encode($code));
		$this->assertEqual(500, $response->code, "Nem megfelelő a kód" . $response->code . " " . $response->content);
		$this->assertEqual($response->content, "Nem megfelelő hosszúságú kód! Csak 2 és 18 karakter közötti engedélyezett!", "Nem megfelelő a hibaüzenet" . $response->content);
		$this->checkNoRowInsert();
		
	}
	
	function test_saveCode_bad_cookie(){
		$this->checkBadCookie(URL_SAVE_CODE);
	}
	
	private function getCodeObject(){
		$code = new stdClass();
		$code->code_type = 'goods_type';
		$code->code_value = 'körtefa';
		return $code;
	}
	
	private function checkNoRowInsert(){
		$db = Data::getInstance($this->unitDbSetting);
		$row = $db->query("select * from code where code_type = 'barmi'")->fetch(PDO::FETCH_OBJ);
		if ($row){
			$this->fail('Insertált sort');
			return;
		}
	}

}
?>