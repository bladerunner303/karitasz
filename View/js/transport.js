//global constanst
var TRANSPORT_URL_REFRESH = '../Controls/listTransports.php';
var TRANSPORT_URL_LIST_CODES = '../Controls/listCodes.php?codeTypes=transport_status';
var TRANSPORT_URL_ONE_OPERATION = '../Controls/listOperations.php';
var TRANSPORT_URL_SAVE_OPERATION = '../Controls/saveOperation.php';
var TRANSPORT_URL_SAVE = '../Controls/saveTransport.php';
var TRANSPORT_URL_PRINT = '../Controls/printTransportForm.php';
var TRANSPORT_URL_CUSTOMER_REFRESH = '../Controls/listCustomers.php';
var TRANSPORT_URL_LIST_CODE_GOOD_TYPES = '../Controls/listCodes.php?codeTypes=goods_type&x=' + new Date().getTime().toString();
var TRANSPORT_URL_NEW_ELEMENT_TYPE = '../Controls/saveCode.php';
var TRANSPORT_URL_SAVE_TRANSPORT_WIZZARD = '../Controls/saveTransportWizzard.php';

//global variables
var transportDataTable;
var transportData;
var transportWizzardCustomer;
var transportWizzardOperation; 

$( document ).ready(function() {
	
	initDatePickerFields();
	handleRefreshTransportListClick();
	handleAddTransportClick();
	handleExportTransportClick();
	initDialogs();
	getTransportSelectItems();
	$('#refresh-transport-list').trigger('click');
});

function initDatePickerFields(){
	$('#transport-date').datepicker(Util.getDefaultDatePicker("-100:+1"));
	$('#find-transport-begin-date').datepicker(Util.getDefaultDatePicker("-20:+0"));
	$('#find-transport-end-date').datepicker(Util.getDefaultDatePicker("-20:+0"));
}

function initDialogs(){
	initTransportAddressDialog();
	initTransportAddressAddDialog();
	initTransportAddressItemsDialog();
	initTransportAddressWizardDialog();
}

function handleRefreshTransportListClick(){
	$('#refresh-transport-list').click(function(){
		var url = TRANSPORT_URL_REFRESH;
		url = Util.addUrlParameter(url, 'begin_date', $('#find-transport-begin-date').val());
		url = Util.addUrlParameter(url, 'end_date', $('#find-transport-end-date').val());
		url = Util.addUrlParameter(url, 'text', $('#find-transport-text').val());
		url = Util.addUrlParameter(url, 'limit', $('#find-transport-result-max').val());
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	Util.clearDataTable(transportDataTable);
		    	var transportTableTemplate = _.template($('#template-transport-table').html());
		    	$('#transport-table > tbody').html(	transportTableTemplate({rows: data}) );	
		    	setTransportTableFormat();
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
		
	});
}

function getTransportSelectItems(){
	$.ajax({
	    url: TRANSPORT_URL_LIST_CODES,
	    type: 'GET',
	    success: function(data){ 
	    	transportStatuses = data.transport_status;
	    },
		error: function(response) {
			Util.handleErrorToConsole();      
	    }
	});
}

function handleAddTransportClick(){
	$('#add-transport').click(function(){
		openTransportDetail(0);
	});
}

function handleExportTransportClick(){
	$('#export-transport').click(function(){
		Util.exportHtmlTableToCsv('Szállítások', 'transport-table');
	});
}

function handleTransportDetailCancelClick(){
	$('#transport-detail-cancel').click(function(){
		$('#transport-detail-general').hide();
		$('#transport').show();
		$('#refresh-transport-list').trigger('click');
		
	});
}

function handleTransportDetailSaveClick(){
	$('#transport-detail-save').click(function(){
		
		if (checkTransportData()){
			saveTransport();
		}
		
	});
}

function handleTransportDetailPrintClick(){
	$('#transport-detail-print').click(function(){
		printTransport($('#transport-selected-id').val());
	});
}

function initTransportDetailButtonEvents(){
	handleTransportDetailCancelClick();
	handleTransportDetailSaveClick();
	handleTransportDetailPrintClick();
}


function initTransportAddressDialog(){
	$('#dialog-transport-address').dialog(Util.getDefaultDialog());
}

function initTransportAddressItemsDialog(){
	$('#dialog-transport-address-item').dialog(Util.getDefaultDialog());
}

function initTransportAddressAddDialog(){
	$('#dialog-transport-address-add').dialog(Util.getDefaultDialog());
}

function initTransportAddressWizardDialog(){
	$('#dialog-transport-address-wizzard').dialog(Util.getDefaultDialog());
	
	//Handle dialog events
	handleTransportAddressWizzardCustomerIdOnChange();
	handleTransportAddressWizzardCancelClick();
	handleTransportAddressWizzardNextClick();
	handleTransportAddressWizzardPrevClick();
	handleTransportAddressWizzardOkClick();
	handleTransportAddressWizzardCancel2Click();
	handletransportAddressWizzardOperationZipChange();
	handleOperationDetailNewElementTypeClick();
	handleTransportAddressWizzardAddClick();
	handleTransportAddressWizzardClearClick();
}

function initTransportSelectElements(selectedValues){
	var selectTransportStatus = $('#transport-detail-status');
	for(var i=0; i< transportStatuses.length; i++){
		selectTransportStatus.append($('<option></option>').val(transportStatuses[i].id).html(transportStatuses[i].code_value));
	}
	
	if (typeof selectedValues != 'undefined'){
		selectTransportStatus.val(Util.isNullOrEmpty(selectedValues.selectedTransportStatus)?'ROGZITETT_TRANSPORT':selectedValues.selectedTransportStatus);
	}
	else {
		selectTransportStatus.val('ROGZITETT_TRANSPORT');
	}
}

function setTransportTableFormat(){
	var defaultLength = 10;
	if(typeof(Storage) !== "undefined") {
		if (!isNaN(localStorage.transport_list_default_page_length) && (localStorage.transport_list_default_page_length != "")){
			defaultLength = localStorage.transport_list_default_page_length;
		}
	}
	
	$('#transport-table').DataTable(Util.getDefaultDataTable (defaultLength));
	
	$('#transport-table_length').css('margin-bottom',  '10px');	
	$('select[name="transport-table_length"]').change(function(){
		if(typeof(Storage) !== "undefined") {
			localStorage.setItem("transport_list_default_page_length", $(this).val());
		}
	});
	
	$('#transport-table').show();
	
}


function openTransportDetail(id){
	$('#transport-detail-address-table').html('');
	$('#transport-selected-id').val(id);
	var transportDetailTemplate = _.template($('#template-transport-detail-general').html());
	transportData = {id: null, //'<script type="text/javascript">var x="v"; alert("xss");</script>', 
						status: null,
						transport_date: $('#server-time').val().substring(0,10),  
						status: null,
						created_info: null, 
						modified_info: null,
						addresses: []};
	$('#transport').hide();
	if (id == 0){
		//Új
		$('#transport-detail-general').html(transportDetailTemplate(transportData));
		initTransportSelectElements();
		reloadTransportAddressTable();
		$('#transport-detail-transport-date').datepicker(Util.getDefaultDatePicker("-20:+1"));
		$('#tr-transport-detail-id').hide();
		$('#tr-transport-detail-created').hide();
		$('#tr-transport-detail-modified').hide();
		initTransportDetailButtonEvents();
	}
	else {
		//Módosítás
		var url = TRANSPORT_URL_REFRESH;
		url = Util.addUrlParameter(url, 'id', id);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	if (data.length != 1){
		    		Util.logConsole('Nem található a művelet');
		    		return;
		    	}
		    	
		    	transportData = data[0];
		    	$('#transport-detail-general').html(transportDetailTemplate(transportData));

				initTransportSelectElements({
					selectedTransportStatus: transportData.status
				});
				reloadTransportAddressTable();
				$('#transport-detail-transport-date').datepicker($.datepicker.regional[ "hu" ]);
				$('#tr-transport-detail-id').show();
				$('#tr-transport-detail-created').show();
				$('#tr-transport-detail-modified').show();
				initTransportDetailButtonEvents();
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
		
		
	}
	$('#transport-detail-general').show();	

}

function openAddress(id){
	
	var url = TRANSPORT_URL_REFRESH;
	url = Util.addUrlParameter(url, 'id', id);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){ 
	    	if (data.length != 1){
	    		Util.logConsole('Nem található a művelet');
	    		return;
	    	}
	    	transportAddressess = data[0].addresses;
	    	if (transportAddressess.length > 0){
	    		var transportAddressesTableTemplate = _.template($('#template-transport-addresses-table').html());
	    		$('#dialog-transport-address-table').html(transportAddressesTableTemplate({rows: transportAddressess, editable: false}));
	    	}
	    	else {
	    		$('#dialog-transport-address-table').html('<p>Nem található a szállításhoz rendelt cím!</p>');
	    	}
	    	$('#dialog-transport-address').dialog('open');
	    	
	    },
		error: function(response) {
			Util.handleErrorToConsole(response);
	       
	    }
	});
	
}

function addAddress(id){
	$('#refresh-waiting-transport-operations-list').trigger('click');
	$('#dialog-transport-address-add').dialog('open');
	$('#transport-date').datepicker();
	$('#transport-detail-address-table').html($('#dialog-transport-address-table').html());
	$('#dialog-transport-address-table').DataTable(Util.getDefaultDataTable (10));
}

function selectTransportItem(id){
	//call for waitingTransport.jss
	var url = TRANSPORT_URL_ONE_OPERATION;
	url = Util.addUrlParameter(url, 'id', id);
	url = Util.addUrlParameter(url, 'limit', 1);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){ 
	    	row = data[0];
	    	transportData.addresses.push({
	    		operation_id : row.id,
	    		zip : row.zip,
	    		city : row.city, 
	    		street : row.street,
	    		address_format: row.full_address_format,
	    		description: row.description,
	    		customer_format: row.customer_format,
	    		priority: row.qualification_local,
	    		status: 'ROGZITETT_TRANSPORT',
	    		status_local: 'Rögzített',
	    		order_indicator: transportData.addresses.length
	    	});
	    	reloadTransportAddressTable();
	    	$('#dialog-transport-address-add').dialog('close');
	    	
	    },
		error: function(response) {
			Util.handleErrorToConsole(response);
	    }
	});
	
}

function reloadTransportData(){
	
	transportData.transport_date = $('#transport-detail-transport-date').val();
	transportData.status = $('#transport-detail-status').val();
}

function removeTransportAddress(id){
	
	if (confirm('A művelet nem visszavonható! Biztos töröljük véglegesen az elemet?')){
		for (var i=0;i<transportData.addresses.length;i++){
			if (transportData.addresses[i].operation_id == id ){
				transportData.addresses.splice(i, 1);
				reloadTransportAddressTable();
				return;
			}
		}
		
	}
}

function setTransportAddressStatus(id, status){
	if (confirm('A művelet hatására az összes alábontott elemet is sikeresre/sikertelenre állítjuk! Biztos ' + ((status == 'BEFEJEZETT_TRANSPORT') ? 'befejezettre' : 'sikertelenre') + ' állítsuk véglegesen az elemet?')){
		for (var i=0;i<transportData.addresses.length;i++){
			if (transportData.addresses[i].operation_id == id ){
				transportData.addresses[i].status = status;
				transportData.addresses[i].status_local = (status == 'BEFEJEZETT_TRANSPORT' ? 'Befejezett': 'Sikertelen'); ///TODO: local név a js-ben. Ezt ki kell szedni és szerver oldalról venni
				
				for (var n=0; n<transportData.addresses[i].items.length; n++){
					transportData.addresses[i].items[n].status = status;
					transportData.addresses[i].items[n].status_local = ((status == 'BEFEJEZETT_TRANSPORT') ? 'Befejezett': 'Sikertelen');
					
				}
				
				reloadTransportAddressTable();
				return;
			}
		}
	}
}

function setTransportAddressItemStatus(id, status){
	if (confirm('Biztos ' + ((status == 'BEFEJEZETT_TRANSPORT') ? 'befejezettre' : 'sikertelenre') + ' állítsuk véglegesen az elemet?')){
		for (var i=0;i<transportData.addresses.length;i++){
			for (var n=0; n<transportData.addresses[i].items.length; n++){
				var currentItem = transportData.addresses[i].items[n];
				if (currentItem.id == id){
					transportData.addresses[i].items[n].status = status;
					transportData.addresses[i].items[n].status_local = ((status == 'BEFEJEZETT_TRANSPORT') ? 'Befejezett': 'Sikertelen');
					 showTransportAddressItems(transportData.addresses[i].id);
				}
			}
		}
	}	
}

function checkTransportData(){
	reloadTransportData();
	return true;
}

function saveTransport(){
	
	var data = JSON.stringify(transportData);
	
	$.ajax({
	    url: TRANSPORT_URL_SAVE,
	    type: 'POST',
	    data: data,
	    success: function(data){ 
	    	Util.showSaveResultDialog(true, 'Sikeres szállítás mentés!');
	    	openTransportDetail(data);
	    },
	    error: function(response) {
	    	Util.handleErrorToDiv(response, $('#transport-save-errors'));
	    	$('#transport-save-errors').show();
	    }
	});	
}

function reloadTransportAddressTable(){
	if (transportData.addresses.length > 0){
		transportData.addresses.sort(function(a, b){return a.order_indicator - b.order_indicator; });
		var transportAddressesTableTemplate = _.template($('#template-transport-addresses-table').html());
		$('#transport-detail-address-table').html(transportAddressesTableTemplate({rows: transportData.addresses, editable: true}));
	}
	else {
		$('#transport-detail-address-table').html('<p>Nincs még mentett címe a szállításnak!</p>');
	}
}

function printTransport(id){
	window.open(TRANSPORT_URL_PRINT + "?id=" + id,'_blank');
}

function moveAddress(order_indicator, orientation){
	
	if ((transportData.addresses.length < 2) || 
	   ((transportData.addresses[transportData.addresses.length-1].order_indicator == order_indicator) && (orientation != 'UP'))
	) {
		return;
	}
	
	for (var i=0;i<transportData.addresses.length; i++){
		if (transportData.addresses[i].order_indicator == order_indicator){
			if (orientation == 'UP'){
				transportData.addresses[i].order_indicator--;
				transportData.addresses[i-1].order_indicator++;
			}
			else {
				transportData.addresses[i].order_indicator++;
				transportData.addresses[i+1].order_indicator--;
			}
			reloadTransportAddressTable();
			return;
		}
	}
	
}

function showTransportAddressItems(transportAddressId){
	
	var transportAddressItems = [];
	for (var i=0;i<transportData.addresses.length; i++){
		if (transportData.addresses[i].id == transportAddressId){
			transportAddressItems = transportData.addresses[i].items;
		}
	}
		
	if (transportAddressItems.length > 0){
		var transportAddressItemsTableTemplate = _.template($('#template-transport-address-item-table').html());
		$('#dialog-transport-address-item-table').html(transportAddressItemsTableTemplate({rows: transportAddressItems}));
	}
	else {
		$('#dialog-transport-address-item-table').html('<p>Nem található a címhez elem!</p>');
	}
	$('#dialog-transport-address-item').dialog('open');
	
}

function addNotes(operationId){
	
	var inputStr = prompt("Megjegyzés füzése a szállításhoz");
	
	if (!Util.isNullOrEmpty(inputStr.trim())){
		//find operationId
		//call for waitingTransport.jss
		var url = TRANSPORT_URL_ONE_OPERATION;
		url = Util.addUrlParameter(url, 'id', operationId);
		url = Util.addUrlParameter(url, 'limit', 1);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	data[0].description += "\r\n" + inputStr;
		    	var newOperationData = JSON.stringify(data[0]);
		    	
		    	$.ajax({
		    	    url: TRANSPORT_URL_SAVE_OPERATION,
		    	    type: 'POST',
		    	    data: newOperationData,
		    	    success: function(data){ 
		    	    	Util.showSaveResultDialog(true, 'Sikeres megjegyzés hozzáadás!');
		    	    },
		    	    error: function(response) {
		    	    	Util.handleErrorToConsole(response);
		    	    	Util.showSaveResultDialog(false, response);
		    	    }
		    	});	
		    	
		    	
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
				Util.showSaveResultDialog(false, response);
		    }
		});
	}
	
}

function addAddressWizard() {
	clearWizardDiv();
	$('#dialog-transport-address-wizzard').dialog('open');
}

function clearWizardDiv(){
	Util.clearElements('#transport-address-wizzard-customer');
	Util.clearElements('#transport-address-wizzard-operation');	
	$('#transport-address-wizzard-prev').trigger('click');
	transportWizzardCustomer = {};
	transportWizzardOperation = {};
	transportWizzardOperation.elements = [];
	initSelectGoodsType();
}

function handleTransportAddressWizzardCustomerIdOnChange(){
	$('#transport-address-wizzard-customer-id').change(function(){
		var id = $(this).val();
		if (!Util.isNullOrEmpty(id)){
			var url = TRANSPORT_URL_CUSTOMER_REFRESH;
			url = Util.addUrlParameter(url, 'id', id);
			url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
			
			$.ajax({
			    url: url,
			    type: 'GET',
			    success: function(data){
			    	if (data.length != 1){
			    		Util.logConsole('Nem található az ügyfél');
				    	Util.showSaveResultDialog(false, 'Nem található az ügyfél!');
				    	$(this).val('');
			    		return;
			    	}
			    	else {
			    		transportWizzardCustomer = data[0];
			    		
			    		$('#transport-address-wizzard-customer-customer-surname').val(transportWizzardCustomer.surname);
			    		$('#transport-address-wizzard-customer-customer-forename').val(transportWizzardCustomer.forename);
			    		$('#transport-address-wizzard-customer-customer-phone').val(transportWizzardCustomer.phone);
			    		$('#transport-address-wizzard-customer-customer-email').val(transportWizzardCustomer.email);
			    		$('#transport-address-wizzard-operation-zip').val(transportWizzardCustomer.zip);
			    		$('#transport-address-wizzard-operation-city').val(transportWizzardCustomer.city);
			    		$('#transport-address-wizzard-operation-street').val(transportWizzardCustomer.street);
			    		$('input[name=transport-address-wizzard-customer-customer-type]').val([transportWizzardCustomer.customer_type]);
			    	}
			    	
			    	
			    },
				error: function(response) {
					Util.handleErrorToConsole(response);
					Util.showSaveResultDialog(false, 'Nem található az ügyfél!');
			    	$(this).val('');
		    		return;
			    }
			});
			
		}		
	});
}

function handleTransportAddressWizzardCancelClick(){
	$('#transport-address-wizzard-cancel').click(function(){
		clearWizardDiv();
		$('#dialog-transport-address-wizzard').dialog('close');
	});
}
	
function handleTransportAddressWizzardNextClick(){
	$('#transport-address-wizzard-next').click(function(){
		
		if (Util.isNullOrEmpty($('#transport-address-wizzard-customer-id').val())){
			transportWizzardCustomer.customer_type = $('input[name=transport-address-wizzard-customer-customer-type]:checked').val();
		}
		
		transportWizzardCustomer.surname = $('#transport-address-wizzard-customer-customer-surname').val();
		transportWizzardCustomer.forename = $('#transport-address-wizzard-customer-customer-forename').val();
		transportWizzardCustomer.phone = $('#transport-address-wizzard-customer-customer-phone').val();
		transportWizzardCustomer.email = $('#transport-address-wizzard-customer-customer-email').val();
		
		//Ellenőrzések
		var requiredFields = [
		                      {field: 'surname', 	local: 'Család név'}, 
		                      {field: 'phone', 		local: 'Telefonszám'},
		                      {field: 'customer_type', local: 'Ügyfél besorolás'}
		                     ];
		var errors = Util.checkRequiredFields(requiredFields, transportWizzardCustomer);
		
		var phonePattern = new RegExp($('#valid-phone-number-regexp').val());
		if (!phonePattern.test(transportWizzardCustomer.phone)){
			errors.push('Érvénytelen telefonszám formátum');
		}
		
		var errorHtml = '';
		for (var i=0; i<errors.length; i++){
			errorHtml += errors[i] + '<br>';
		}
		
		if (Util.isNullOrEmpty(errorHtml)){
			$('#transport-address-wizzard-customer-errors').hide();
			$('#transport-address-wizzard-customer').hide();
			$('#transport-address-wizzard-operation').show();
		
		}
		else {
			$('#transport-address-wizzard-customer-errors-div').html(errorHtml);
			$('#transport-address-wizzard-customer-errors').show();
			return false;
		}
		
	});
}

function handleTransportAddressWizzardPrevClick(){
		//Oldal váltás
	$('#transport-address-wizzard-prev').click(function(){
		$('#transport-address-wizzard-customer').show();
		$('#transport-address-wizzard-operation').hide();
	});
		
}

function handleTransportAddressWizzardOkClick(){
	
	$('#transport-address-wizzard-save').click(function(){
		//Mentés 
		transportWizzardCustomer.zip = $("#transport-address-wizzard-operation-zip").val();
		transportWizzardCustomer.city = $("#transport-address-wizzard-operation-city").val(); 
		transportWizzardCustomer.street = $("#transport-address-wizzard-operation-street").val();
		
		
		var requiredFields = [
		                      {field: 'zip', 		local: 'Irányítószám'},
		                      {field: 'city', 		local: 'Város'},
		                      {field: 'street',		local: 'Utca/házszám'}
		                     ];
		var errors = Util.checkRequiredFields(requiredFields, transportWizzardCustomer);
		
		var errorHtml = '';
		for (var i=0; i<errors.length; i++){
			errorHtml += errors[i] + '<br>';
		}
		
		if (Util.isNullOrEmpty(errorHtml)){
			$('#transport-address-wizzard-operation-errors').hide();
			//Beküldés
			var requestData = {};
			requestData.operation = transportWizzardOperation;
			requestData.customer = transportWizzardCustomer;
			requestData.transportId = $('#transport-detail-id').text();
			var data = JSON.stringify(requestData);
			
	    	$.ajax({
	    	    url: TRANSPORT_URL_SAVE_TRANSPORT_WIZZARD,
	    	    type: 'POST',
	    	    data: data,
	    	    success: function(data){ 
	    	    	//dialog lezárás
	    			$('#transport-address-wizzard-cancel').trigger('click');
	    	    },
	    	    error: function(response) {
	    	    	Util.handleErrorToConsole(response.responseText);
	    	    	Util.showSaveResultDialog(false, response.responseText);
	    	    }
	    	});	

			
		}
		else {
			$('#transport-address-wizzard-operation-errors-div').html(errorHtml);
			$('#transport-address-wizzard-operation-errors').show();
			return false;
		}
		
	});
		
}

function handleTransportAddressWizzardCancel2Click(){
	$('#transport-address-wizzard-cancel2').click(function(){
		$('#transport-address-wizzard-cancel').trigger('click');
	});
}

function handletransportAddressWizzardOperationZipChange(){
	$('#transport-address-wizzard-operation-zip').change(function(){
		var zip = $(this).val();
		if (!Util.isNullOrEmpty(zip)){
			$('#transport-address-wizzard-operation-city').val(Util.nvl(zips[zip], ''));
		}
	});
}

function initSelectGoodsType(selectedValue){
	$.ajax({
	    url: TRANSPORT_URL_LIST_CODE_GOOD_TYPES,
	    type: 'GET',
	    success: function(data){
	    	goodsTypes = data.goods_type;
	    	var selectGoodsType = $('#transport-address-wizzard-add-element-type');
	    	selectGoodsType.html('');
	    	selectGoodsType.append($('<option></option>').val('').html(' '));
	    	for(var i=0; i< goodsTypes.length; i++){
	    		selectGoodsType.append($('<option></option>').val(goodsTypes[i].id).html(goodsTypes[i].code_value));
	    	}
	    	if (!Util.isNullOrEmpty(selectedValue)){
		    	selectGoodsType.val(selectedValue);	
	    	}
	    	selectGoodsType.trigger('change');
	    },
		error: function(response) {
			Util.handleErrorToConsole();      
	    }
	});
	
}

function handleOperationDetailNewElementTypeClick(){
	$('#transport-address-wizzard-new-element-type').click(function(){
		
		///TODO: prompt ablak kicserélése
		var newElement = prompt("Ad meg kérlek az új elemet");
		if (newElement != null) {
		    
			if ((newElement.length < 2) || (newElement.length > 18)){
				alert('Nem megfelelő hosszúságú kód! Csak 2 és 18 karakter közötti engedélyezett!');
				return;
			}
			
			var data = JSON.stringify({code_type: 'goods_type', code_value: newElement});
			
			$.ajax({
			    url: TRANSPORT_URL_NEW_ELEMENT_TYPE,
			    type: 'POST',
			    data: data,
			    success: function(data){ 
			    	initSelectGoodsType(data);
			    },
				error: function(response) {
					alert(response.responseText);
					Util.handleErrorToConsole();      
			    }
			});
			
		}
		
	});
}

function handleTransportAddressWizzardAddClick(){
	$('#transport-address-wizzard-add').click(function(){
		//Ellenőrzés
		var element = {};
		element.name = $.trim($('#transport-address-wizzard-add-element-name').val());
		element.goods_type = $('#transport-address-wizzard-add-element-type').val();
		element.typeName = $('#transport-address-wizzard-add-element-type option:selected').text();
		element.number = parseInt($('#transport-address-wizzard-add-element-type-number').val(),10);
		element.id = null;
		element.status = 'FOLYAMATBAN';
		element.detail_id = null;
		element.detail_files = [];
		
		
		if (Util.isNullOrEmpty(element.goods_type)) {
			alert('A kiválasztott elemnek nincs típusa. Enélkül nem menthető');
			return;
		}
		
		/*
		if (Util.isNullOrEmpty(element.name)) {
			alert('A kiválasztott elemnek nincs leírása. Enélkül nem menthető');
			return;
		}
		*/
		
		transportWizzardOperation.elements.push(element);
		refreshTransportAddressWizzardElementsTable();
	});
}

function handleTransportAddressWizzardClearClick(){
	$('#transport-address-wizzard-clear').click(function(){
		Util.clearElements('#transport-address-wizzard-add-element-table');
	});
}

function removeTransportAddressWizzardElement(elementType, elementNumber, elementName){
	for (var i=0;i<transportWizzardOperation.elements.length;i++){
		if ((transportWizzardOperation.elements[i].type == elementType) &&
			(transportWizzardOperation.elements[i].number == elementNumber) &&
			(transportWizzardOperation.elements[i].name == elementName))
		{
			transportWizzardOperation.elements.splice(i, 1);
		}
	}
	refreshTransportAddressWizzardElementsTable();
}

function refreshTransportAddressWizzardElementsTable(){
	if (transportWizzardOperation.elements.length != 0){
		var elementsTableTemplate = _.template($('#template-transport-address-wizzard-elements-table').html());
		$('#transport-address-wizzard-elements').html(elementsTableTemplate({rows: transportWizzardOperation.elements}));
	}
	else {
		$('#transport-address-wizzard-elements').html('');
	}
	
}
