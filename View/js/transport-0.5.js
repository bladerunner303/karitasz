//global constanst
var TRANSPORT_URL_REFRESH = '../Controls/listTransports.php';
var TRANSPORT_URL_LIST_CODES = '../Controls/listCodes.php?codeTypes=transport_status';
var TRANSPORT_URL_ONE_OPERATION = '../Controls/listOperations.php';
var TRANSPORT_URL_SAVE = '../Controls/saveTransport.php';

//global variables
var transportDataTable;
var transportData;

$( document ).ready(function() {
	
	initDatePickerFields();
	handleRefreshTransportListClick();
	handleAddTransportClick();
	initTransportAddressDialog();
	initTransportAddressAddDialog();
	getTransportSelectItems();
	$('#refresh-transport-list').trigger('click');
});

function initDatePickerFields(){
	$('#transport-date').datepicker();
	$('#find-transport-begin-date').datepicker($.datepicker.regional[ "hu" ]);
	$('#find-transport-end-date').datepicker($.datepicker.regional[ "hu" ]);
}

function handleRefreshTransportListClick(){
	$('#refresh-transport-list').click(function(){
		var url = TRANSPORT_URL_REFRESH;
		url = Util.addUrlParameter(url, 'begin_date', $('#find-transport-begin-date').val());
		url = Util.addUrlParameter(url, 'end_date', $('#find-transport-end-date').val());
		url = Util.addUrlParameter(url, 'customer', $('#find-transport-customer').val());
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

function handleTransportDetailCancelClick(){
	$('#transport-detail-cancel').click(function(){
		$('#transport-detail-general').hide();
		$('#transport').show();
		$('#refresh-transport-list').trigger('click');
		
	});
}

function handleTransportDetailSaveClick(){
	$('#transport-detail-save').click(function(){
		//alert('Ezt még implementálni kell');
		
		if (checkTransportData()){
			saveTransport();
			//$('#transport-detail-cancel').trigger('click');
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
	$('#dialog-transport-address').dialog({
		 autoOpen: false, 
		 modal: true,
		 width: 'auto'
	});
}

function initTransportAddressAddDialog(){
	$('#dialog-transport-address-add').dialog({
		 autoOpen: false, 
		 modal: true,
		 width: 'auto'
	});
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
		$('#transport-detail-transport-date').datepicker($.datepicker.regional[ "hu" ]);
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
	    		status_local: 'Rögzített'	
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

function setSuccesfulTransportAddress(id){
	if (confirm('Biztos befejezettre állítsuk véglegesen az elemet?')){
		for (var i=0;i<transportData.addresses.length;i++){
			if (transportData.addresses[i].operation_id == id ){
				transportData.addresses[i].status = 'BEFEJEZETT_TRANSPORT';
				transportData.addresses[i].status_local = 'Befejezett'; ///TODO: local név a js-ben. Ezt ki kell szedni és szerver oldalról venni
				reloadTransportAddressTable();
				return;
			}
		}
	}
}

function setUnsuccesfulTransportAddress(id){
	if (confirm('Biztos sikertelenre állítsuk véglegesen az elemet?')){
		for (var i=0;i<transportData.addresses.length;i++){
			if (transportData.addresses[i].operation_id == id ){
				transportData.addresses[i].status = 'SIKERTELEN_TRANSPORT';
				transportData.addresses[i].status_local = 'Sikertelen'; ///TODO: local név a js-ben. Ezt ki kell szedni és szerver oldalról venni
				reloadTransportAddressTable();
				return;
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
	    	Util.handleErrorToDiv(response, $('#operation-detail-save-errors-div'));
	    	$('#operation-detail-save-errors').show();
	    }
	});	
}

function reloadTransportAddressTable(){
	if (transportData.addresses.length > 0){
		var transportAddressesTableTemplate = _.template($('#template-transport-addresses-table').html());
		$('#transport-detail-address-table').html(transportAddressesTableTemplate({rows: transportData.addresses, editable: true}));
	}
	else {
		$('#transport-detail-address-table').html('<p>Nincs még mentett eleme a kérvénynek/felajánlásnak!</p>');
	}
}

function printTransport(id){
	alert('Itt jön majd a szállítóknak készülő doksi');
}