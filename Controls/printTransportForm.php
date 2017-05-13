<?php
//session_start();
require_once '../Util/Loader.php';

ErrorHandler::register();

if (!SessionUtil::validSession()){
	JsonParser::sendSessionExpired();
	return;
}
SessionUtil::logControlRun(basename(__FILE__));

$id =  (!empty($_GET['id'])) ? $_GET['id'] : null;
define ("TEMPLATE_PATH", "../PdfTemplates/transportForm.template");

$html = file_get_contents(TEMPLATE_PATH);

$transportFinder = new Transport();
$transportFinder->setId($id);
$transports = $transportFinder->find('1900-01-01', '2100-01-01', null);

$transportAddressFinder = new TransportAddress();
$transportAddressFinder->setTransportId($id);
$transportAddresses = $transportAddressFinder->find();
// Logger::info(json_encode($transportAddresses));

if (count($transports) == 0 ){
	renderHtml("Nem található a kért szállítás!");
}
elseif (count($transportAddresses) == 0){
	renderHtml("Nem található cím a szállításhoz!");
}
else {
	$transport = $transports[0];
	$html = str_replace('<%%transportDate%%>', $transport->transport_date, $html);		
	$html = str_replace('<%%transportNumber%%>', $transport->id_format, $html);		
	$html = str_replace('<%%creator%%>', $transport->creator, $html);
	$html = str_replace('<%%transportItems%%>', getTransportItemsTable($transportAddresses), $html);
	renderHtml($html);
}	

function getTransportItemsTable($items){

	$template = '<tr style="width: 1100px;  background-color: white;>
				<td style="width: 200px; border-right: solid 1px;border-bottom: solid 1px;"><%%address%%></td>
				<td style="width: 200px; border-right: solid 1px;border-bottom: solid 1px;"><%%customer%%></td>
				<td style="width: 100px; border-right: solid 1px;border-bottom: solid 1px;"><%%phone%%></td>
				<td style="width: 200px; border-right: solid 1px;border-bottom: solid 1px;"><%%description%%></td>
				<td style="width: 300px; border-right: solid 1px;border-bottom: solid 1px;"><%%items%%></td>
				</tr>';
	$html = '';
	foreach ($items as $index => $item) {
		$html .= $template;
		$html = str_replace('<%%address%%>', $item->address_format, $html);
		$html = str_replace('<%%customer%%>', $item->customer_format, $html);
		$html = str_replace('<%%phone%%>', $item->customer_phone, $html);
		$html = str_replace('<%%description%%>', $item->operation_description, $html);
		
		$itemsHtml = '';
		$addressItems = TransportAddress::findAddressItems($item->id);
		foreach ($addressItems as $addressItem) {
			$itemsHtml .= $addressItem->name_format . '<br>';
		}
		$html = str_replace('<%%items%%>', $itemsHtml, $html);
	}
	return $html;
}

function renderHtml ($html){
	$html2pdf = new HTML2PDF('L', 'A4', 'fr'); //'P' - álló, 'L' - Fekvő
	$html2pdf->pdf->SetDisplayMode('real');
	$html2pdf->writeHTML($html);
	$html2pdf->Output('transport.pdf');
	return true;
}
?>