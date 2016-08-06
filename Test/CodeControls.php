<?php

define ('URL_LIST_CODES', 'listCodes.php');


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

}
?>