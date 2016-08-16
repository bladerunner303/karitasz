
//global constans
var CUSTOMER_URL_LIST_CODES = '../Controls/listCodes.php?codeTypes=status;customer_qualification&x=' + new Date().getTime().toString();
var CUSTOMER_URL_REFRESH = '../Controls/listCustomers.php';
var CUSTOMER_URL_SIMILAR = '../Controls/similarCustomers.php';
var CUSTOMER_URL_SAVE = '../Controls/saveCustomer.php';
var CUSTOMER_URL_HISTORY = '../Controls/listCustomerHistory.php';
var CUSTOMER_URL_LIST_OPERATIONS = '../Controls/listOperations.php';

//global variables
var customerDataTable;
var similarCustomerDataTable;
var customerData;

$( document ).ready(function() {

	handleRefreshCustomerListClick();
	handleFindCustomerTypeRadioChange();
	handleAddCustomerClick();
	$('#refresh-customer-list').trigger('click');
	getCustomerSelectItems();
	initCustomerDialogs();
	$('#find-customer-result-max').numericField(false);
});

function initCustomerDialogs(){
	$('#dialog-similar-customers').dialog(Util.getDefaultDialog());
	handleSimilarCustomerSaveClick();
}

function getCustomerSelectItems(){
	$.ajax({
	    url: CUSTOMER_URL_LIST_CODES,
	    type: 'GET',
	    success: function(data){ 
	    	statuses = data.status;
	    	customerQualifications = data.customer_qualification;
	    },
		error: function(response) {
			Util.handleErrorToConsole(response);
	       
	    }
	});
}

function initCustomerSelectElements(selectedStatus, selectedQualification){
	
	var selectCustomerStatus = $('#customer-detail-customer-status');
	var selectQualification = $('#customer-detail-qualification');
	
	for(var i=0; i< statuses.length; i++){
		selectCustomerStatus.append($('<option></option>').val(statuses[i].id).html(statuses[i].code_value));
	}
	
	for(var i=0; i< customerQualifications.length; i++){
		selectQualification.append($('<option></option>').val(customerQualifications[i].id).html(customerQualifications[i].code_value));
	}

	if (!Util.isNullOrEmpty(selectedStatus)){
		selectCustomerStatus.val(selectedStatus);
	}
	
	if (!Util.isNullOrEmpty(selectedQualification)){
		selectQualification.val(selectedQualification);
	}
}
	
function setCustomerTableFormat(){
	
	var defaultLength = 10;
	if(typeof(Storage) !== "undefined") {
		if (!isNaN(localStorage.customer_list_default_page_length) && (localStorage.customer_list_default_page_length != "")){
			defaultLength = localStorage.customer_list_default_page_length;
		}
	}
	
	customerDataTable = $('#customer-table').DataTable(Util.getDefaultDataTable (defaultLength));
	
	$('#customer-table_length').css('margin-bottom',  '10px');	
	$('select[name="customer-table_length"]').change(function(){
		if(typeof(Storage) !== "undefined") {
			localStorage.setItem("customer_list_default_page_length", $(this).val());
		}
	});
	
	$('#customer-table').show();
}

function openCustomerDetail(id) {
	
	$('#customer-selected-id').val(id);
	var customerDetailTemplate = _.template($('#template_customer_detail').html());
	customerData = {id: null, //'<script type="text/javascript">var x="v"; alert("xss");</script>', 
						surname: null,
						forename: null, 
						zip: null, 
						city: null, 
						street: null, 
						phone: null,
						additional_contact: null, 
						additional_contact_phone: null,
						description: null,
						tax_number: null, 
						tb_number: null, 
						birth_place: null, 
						birth_date: null,
						created_info: null, 
						modified_info: null};
	$('#customer').hide();
	
	if (id == 0){
		//Új
		$('#customer-detail-general').html(customerDetailTemplate(customerData));
		initCustomerDetailsEvents();
		initCustomerSelectElements(null, 'NORMAL');
		initCustomerDetailsNumericField();
		$('#tr-customer-detail-id').hide();
		$('input[name=customer-detail-customer-type]').removeAttr( "disabled" );
		$('#tr-customer-detail-created').hide();
		$('#tr-customer-detail-modified').hide();
		$('#href-customer-detail-operation').hide();
		$('#href-customer-detail-log').hide();
	}
	else {
		//Módosítás
		var url = CUSTOMER_URL_REFRESH;
		url = Util.addUrlParameter(url, 'id', id);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	if (data.length != 1){
		    		Util.logConsole('Nem található az ügyfél');
		    		return;
		    	}
		    	
		    	customerData = data[0];
		    	$('#customer-detail-general').html(customerDetailTemplate(customerData));
		    	initCustomerDetailsEvents();
		    	initCustomerSelectElements(customerData.status, customerData.qualification);
		    	initCustomerDetailsNumericField();
		    	$('#tr-customer-detail-id').show();
		    	$('input[name=customer-detail-customer-type]').val([customerData.customer_type]);
		    	$('input[name=customer-detail-customer-type]').attr('disabled', 'disabled');
				$('#tr-customer-detail-created').show();
				$('#tr-customer-detail-modified').show();
				$('#href-customer-detail-operation').show();
				$('#href-customer-detail-log').show();
				displayCustomerLastOperation(id);
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
		
		$('#refresh-operation-list').trigger('click'); //operation.php-ről származó
		
	}
	$('#customer-detail-tabs').tabs();
	$('#customer-detail').show();
	$('#dialog-similar-customers').dialog('close');
	refreshCustomerDetailLog(id);
}

function initCustomerDetailsEvents(){
	handleCustomerSaveClick();
	handleCustomerZipChange();
	handleCustomerCancelClick();
}

function initCustomerDetailsNumericField(){
  	$('#customer-detail-birth-date-year').numericField(false);	
	$('#customer-detail-birth-date-month').numericField(false);
	$('#customer-detail-birth-date-day').numericField(false);
}

function handleCustomerSaveClick(){

	$('#customer-save').click(function(){	
		
		if (checkCustomerData()){
			checkSimilarCustomers();
		}
	});
}


function handleSimilarCustomerSaveClick(){
	$('#similar-customer-save').click(function(){
		$('#dialog-similar-customers').dialog('close');
		saveCustomer();
	});
}

function handleCustomerCancelClick(){
	$('#customer-cancel').click(function(){
		$('#customer-detail').hide();
		$('#customer').show();
		$('#refresh-customer-list').trigger('click');
		
	});
}

function handleCustomerZipChange(){
	$('#customer-detail-zip').change(function(){
		refreshCustomerDetailCity();
	});
}

function handleAddCustomerClick(){
	$('#add-customer').click(function(){
		openCustomerDetail(0);
	});
}

function handleRefreshCustomerListClick(){
	$('#refresh-customer-list').click(function(){
		var url = CUSTOMER_URL_REFRESH;
		url = Util.addUrlParameter(url, 'customer_type', $('input[name=find-customer-type]:checked').val());
		url = Util.addUrlParameter(url, 'text', $('#find-customer-text').val());
		url = Util.addUrlParameter(url, 'limit', $('#find-customer-result-max').val());
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	Util.clearDataTable(customerDataTable);
		    	var customerTableTemplate = _.template($('#template_customer_table').html());
		    	$('#customer-table > tbody').html(customerTableTemplate({rows: data}));
		    	setCustomerTableFormat();
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
		
	});
}

function handleFindCustomerTypeRadioChange(){
	$('input[type=radio][name=find-customer-type]').change(function() {
		$('#refresh-customer-list').trigger('click');
	});
}

function reloadCustomerData(){
	//Adatok visszagyűjtése
	customerData.id = $('#customer-selected-id').val(); 
	customerData.customer_type = $('input[name=customer-detail-customer-type]:checked').val();
	customerData.surname = $.trim($('#customer-detail-surname').val());
	customerData.forename = $.trim($('#customer-detail-forename').val()); 
	customerData.zip = $.trim($('#customer-detail-zip').val()); 
	customerData.city = $.trim($('#customer-detail-city').val()); 
	customerData.street = $.trim($('#customer-detail-street').val()); 
	customerData.phone = $.trim($('#customer-detail-phone').val().split('-').join('').split('/').join('').split(' ').join(''));
	customerData.additional_contact = $.trim($('#customer-detail-additional-contact').val()); 
	customerData.additional_contact_phone = $.trim($('#customer-detail-additional-contact-phone').val());
	customerData.description = $.trim($('#customer-detail-description').val());
	customerData.status = $.trim($('#customer-detail-customer-status').val());
	customerData.qualification = $.trim($('#customer-detail-qualification').val());
	customerData.tax_number = $.trim($('#customer-detail-tax-number').val());
	customerData.tb_number = $.trim($('#customer-detail-tb-number').val());
	customerData.birth_date = Util.lpad($('#customer-detail-birth-date-year').val(), 4, '0') + '-' + 
								Util.lpad($('#customer-detail-birth-date-month').val(), 2, '0') + '-' + 
								Util.lpad($('#customer-detail-birth-date-day').val(), 2, '0')  ;
	if (customerData.birth_date == '0000-00-00'){
		customerData.birth_date = null;
	}
	customerData.birth_place = $.trim($('#customer-detail-birth-place').val());
}

function checkCustomerData(){
	reloadCustomerData();
	var requiredFields = [
	                      {field: 'surname', 	local: 'Család név'}, 
	                      {field: 'zip', 		local: 'Irányítószám'},
	                      {field: 'city', 		local: 'Város'},
	                      {field: 'street', 	local: 'Utca/házszám'},
	                      {field: 'phone', 		local: 'Telefonszám'}
	                     ];
	var errors = Util.checkRequiredFields(requiredFields, customerData);
	
	var phonePattern = new RegExp($('#valid-phone-number-regexp').val());
	if (!phonePattern.test(customerData.phone)){
		errors.push('Érvénytelen telefonszám formátum');
	}
	
	if ((!Util.isNullOrEmpty(customerData.birth_date)) && (isNaN(Date.parse(customerData.birth_date)))) {
		errors.push('Érvénytelen születési dátum');
	}
	
	var errorHtml = '';
	for (var i=0; i<errors.length; i++){
		errorHtml += errors[i] + '<br>';
	}
	
	if (Util.isNullOrEmpty(errorHtml)){
		$('#customer-save-errors').hide();
		return true;
	}
	else {
		$('#customer-detail-save-errors-div').html(errorHtml);
		$('#customer-save-errors').show();
		return false;
	}

}

function checkSimilarCustomers(){
	
	var id = $('#customer-selected-id').val();
	if (id == 0){
		var url = CUSTOMER_URL_SIMILAR;
		url = Util.addUrlParameter(url, 'id', id);
		url = Util.addUrlParameter(url, 'phone', customerData.phone);
		url = Util.addUrlParameter(url, 'surname', customerData.surname);
		url = Util.addUrlParameter(url, 'forename', customerData.forename);
		url = Util.addUrlParameter(url, 'zip', customerData.zip);
		url = Util.addUrlParameter(url, 'street', customerData.street);
		url = Util.addUrlParameter(url, 'tax_number', customerData.tax_number);
		url = Util.addUrlParameter(url, 'tb_number', customerData.tb_number);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	if (data.length > 0){
		    		displaySimilarCustomers(data);
		    	}
		    	else {
		    		saveCustomer();
		    	}	    	
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);       
		    }
		});
	}
	else {
		//Módosításnál nincs vizsgálat
		saveCustomer();
	}
	
}

function displaySimilarCustomers(data){
	
	Util.clearDataTable(similarCustomerDataTable);
	var similarCustomerTableTemplate = _.template($('#template-similar-customer-table').html());
	$('#table-similar-customer > tbody').html(similarCustomerTableTemplate({rows: data}));
	similarCustomerDataTable = $('#table-similar-customer').DataTable({
		searching:   false,
	    paging: false
	});
	$('#dialog-similar-customers').dialog('open');
}

function saveCustomer(){
	$('#customer-save-errors').hide();
	var data = JSON.stringify(customerData);
	
	$.ajax({
	    url: "../Controls/saveCustomer.php",
	    type: 'POST',
	    data: data,
	    success: function(data){ 
	    	Util.showSaveResultDialog(true, 'Sikeres ügyfél mentés!');
	    	openCustomerDetail(data);
	    },
	    error: function(response) {
	    	Util.handleErrorToDiv(response, $('#customer-detail-save-errors-div'));
	    	$('#customer-save-errors').show();
	    }
	});	
}

function refreshCustomerDetailCity(){
	var zip = $('#customer-detail-zip').val();
	if (!Util.isNullOrEmpty(zip)){
		$('#customer-detail-city').val(Util.nvl(zips[zip], ''));
	}
}

function refreshCustomerDetailLog(id){
	if (id == 0){
		$('#customer-detail-log').html('');
	}
	else {
		var url = CUSTOMER_URL_HISTORY;
		url = Util.addUrlParameter(url, 'id', id);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
			    	var customerHistoryTableTemplate = _.template($('#template-customer-history').html());
			    	$('#customer-detail-log').html(customerHistoryTableTemplate({rows: data}));
			    },
				error: function(response) {
					Util.handleErrorToConsole(response);
			    }
		});
	}
}

function displayCustomerLastOperation(customerId){
	
	$('#customer-warning-request').hide();
	var url = CUSTOMER_URL_LIST_OPERATIONS;
	url = Util.addUrlParameter(url, 'customer_id', customerId);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){
	    		var lastOperationTime = '2000-01-01 01:01:01';
	    		for (var i=0; i<data.length; i++){
	    			if ((data[i].operation_type == 'KERVENYEZES') && 
	    				(data[i].status == 'BEFEJEZETT') && 
	    				(data[i].created > lastOperationTime)) {
	    				lastOperationTime = data[i].created;
	    			}
	    		}
	    		
	    		var requestLimitDate = new Date($('#server-time').val());
	    		requestLimitDate.setDate(requestLimitDate.getMonth() - parseInt($('#operation-max-repeat-month').val(), 10));
	    		if (new Date(lastOperationTime) > requestLimitDate){
	    			$('#customer-warning-request-date').text(lastOperationTime.substring(0,10));
	    			$('#customer-warning-request').show();
	    		}
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		    }
	});
}



