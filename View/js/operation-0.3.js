
//global constans
var OPERATION_URL_LIST_CODES = '../Controls/listCodes.php?codeTypes=operation_status;goods_type;neediness_level;sender;income_type&x=' + new Date().getTime().toString();
var OPERATION_URL_LIST_REFRESH = '../Controls/listOperations.php';
var OPERATION_URL_UPLOAD_ATTACHEMENT = '../Controls/uploadOperationAttachment.php';
var OPERATION_URL_LIST_ATTACHMENT = '../Controls/listOperationFiles.php';
var OPERATION_URL_REMOVE_ATTACHMENT = '../Controls/removeOperationAttachment.php';
var OPERATION_URL_DOWNLOAD_ATTACHMENT = '../Controls/downloadFile.php';
var OPERATION_URL_SAVE_OPERATION = "../Controls/saveOperation.php";

//global variables
var operationDataTable;
var operationData = {};

$( document ).ready(function() {
	initNumericFields();
	handleRefreshOperationListClick();
	handleAddOperationClick();
	handleOperationDetailAddElementSaveClick();
	handleOperationDetailAddElementCancelClick();
	handleOperationDetailUploadclick();
	$('#refresh-operation-list').trigger('click');
	initOperationDialogs();
	getOperationSelectItems();
});

function handleRefreshOperationListClick(){
	$('#refresh-operation-list').click(function(){
		
		var url = OPERATION_URL_LIST_REFRESH;
		
		if (getSiteType() == null) {
			url = Util.addUrlParameter(url, 'customer_id', Util.nvl($('#customer-selected-id').val(), '')); //Beágyazó oldalról származó field
		}
		else {
			url = Util.addUrlParameter(url, 'customer', $('#find-operation-customer').val());
			url = Util.addUrlParameter(url, 'operation_type', getSiteType());	
		}
		
		url = Util.addUrlParameter(url, 'status', $('#find-operation-status').val());
		url = Util.addUrlParameter(url, 'wait_callback', $('#find-operation-callback').is(':checked')?'Y':'N');
		url = Util.addUrlParameter(url, 'limit', $('#find-operation-result-max').val());
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	Util.clearDataTable(operationDataTable);
		    	var operationTableTemplate = _.template($('#template-operation-table').html());
		    	$('#operation-table > tbody').html(	operationTableTemplate({rows: data}));
		    	setOperationTableFormat();
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		    }
		});
		
		
	});
}

function handleAddOperationClick(){
	$('#add-operation').click(function(){
		openOpertaionDetail(0);
	});
}

function handleOperationCustomerFindClick(){
	$('#operation-detail-customer-find').click(function(){
		openCustomerDialog();
	});
}

function handleOperationDetailCancelClick(){
	$('#operation-detail-cancel').click(function(){
		$('#operation-detail').hide();
		$('#operation').show();
		$('#refresh-operation-list').trigger('click');
		
	});
}

function handleOperationDetailSaveClick(){
	$('#operation-detail-save').click(function(){
		if (checkOperationData()){
			checkOtherOperations();
		}
	});
}

function handleOperationDetailNewElementClick(){
	$('#operation-detail-new-element').click(function(){
		$('#dialog-add-element').dialog('open');
	});
}

function handleOperationDetailAddElementSaveClick(){
	$('#operation-detail-add-element-save').click(function(){

		var elementName = $.trim($('#operation-detail-add-element-name').val());
		var elementType = $('#operation-detail-add-element-type').val();
		var elementTypeName = $('#operation-detail-add-element-type option:selected').text();
		
		operationData.operationDetails.push({
			name: elementName,
			goods_type: elementType,
			goods_type_local: elementTypeName,
			status: 'ROGZITETT',
			status_local: 'Rögzített',
			order_indicator: operationData.operationDetails.length
		});
		
		$('#operation-detail-add-element-cancel').trigger('click');
		reloadOperationDetailsTable();
	});
}

function handleOperationDetailAddElementCancelClick(){
	$('#operation-detail-add-element-cancel').click(function(){
		$('#operation-detail-add-element-name').val('');
		$('#dialog-add-element').dialog('close');
	});
}

function handleOperationDetailUploadclick(){
	$("#operation-upload").click(function(){
		var fileData = $('#operation-userfile').prop('files')[0];   
	    var formData = new FormData();                  
	    formData.append('userfile', fileData);               
	    var url = OPERATION_URL_UPLOAD_ATTACHEMENT;
	    url = Util.addUrlParameter(url, 'operation_id', operationData.id);
	    $.ajax({
	                url: url, 
	                dataType: 'text', 
	                cache: false,
	                contentType: false,
	                processData: false,
	                data: formData,                         
	                type: 'post',
	                success: function(){
	                    refreshOperationDetailAttachment();
	                    $('#operation-userfile').val('');
	                },
	        		error: function(response) {
	        			Util.handleErrorToConsole(response);
	        			Util.showSaveResultDialog(false, response.responseText);
	        	    }
	     });
	});
} 

function getOperationSelectItems(){
	$.ajax({
	    url: OPERATION_URL_LIST_CODES,
	    type: 'GET',
	    success: function(data){ 
	    	operationStatus = data.operation_status;
	    	goodsTypes = data.goods_type;
	    	senders = data.sender;
	    	incomeTypes = data.income_type;
	    	needinessLevels = data.neediness_level;
	    	initOperationSelectElements();
	    },
		error: function(response) {
			Util.handleErrorToConsole();      
	    }
	});
}

function initNumericFields(){
	$('#find-operation-result-max').numericField(false);
	$('#operation-detail-income').numericField(true);
	$('#operation-detail-others-income').numericField(true);
}

function initOperationDialogs(){
	initAddElementDialog();
	initCustomerDialog();
}

function initAddElementDialog(){
	$('#dialog-add-element').dialog({
		 autoOpen: false, 
		 modal: true,
		 width: 'auto'
	});
}

function initCustomerDialog(){
	$('#dialog-customer').dialog({
		 autoOpen: false, 
		 modal: true,
		 width: 'auto'
	});
}

function initOperationFormButtons(){
	
	handleOperationDetailSaveClick();
	handleOperationDetailNewElementClick();
	handleOperationCustomerFindClick();
	handleOperationDetailCancelClick();
}

function initOperationSelectElements(selectedValues) {
	
	var selectFinderStatus = $('#find-operation-status');
	var selectOperationStatus = $('#operation-detail-status');
	var selectOperationSender = $('#operation-detail-sender');
	var selectOperationIncomeType = $('#operation-detail-income-type');
	var selectOperationNeedinessLevel  = $('#operation-detail-neediness-level');
	var selectGoodsType = $('#operation-detail-add-element-type');
	
	selectFinderStatus.append($('<option></option>').val('').html(' '));
	selectOperationSender.append($('<option></option>').val('').html(' '));
	selectOperationIncomeType.append($('<option></option>').val('').html(' '));
	selectOperationNeedinessLevel.append($('<option></option>').val('').html(' '));
	
	for(var i=0; i< operationStatus.length; i++){
		selectFinderStatus.append($('<option></option>').val(operationStatus[i].id).html(operationStatus[i].code_value));
		selectOperationStatus.append($('<option></option>').val(operationStatus[i].id).html(operationStatus[i].code_value));
	}
	
	for(var i=0; i< goodsTypes.length; i++){
		selectGoodsType.append($('<option></option>').val(goodsTypes[i].id).html(goodsTypes[i].code_value));
	}
	
	for(var i=0; i< senders.length; i++){
		selectOperationSender.append($('<option></option>').val(senders[i].id).html(senders[i].code_value));
	}
	
	for(var i=0; i< incomeTypes.length; i++){
		selectOperationIncomeType.append($('<option></option>').val(incomeTypes[i].id).html(incomeTypes[i].code_value));
	}
	
	for(var i=0; i< needinessLevels.length; i++){
		selectOperationNeedinessLevel.append($('<option></option>').val(needinessLevels[i].id).html(needinessLevels[i].code_value));
	}
	
	if (typeof selectedValues != 'undefined'){

		selectOperationStatus.val(Util.isNullOrEmpty(selectedValues.selectedOperationStatus)?'ROGZITETT':selectedValues.selectedOperationStatus);
		selectOperationSender.val(Util.isNullOrEmpty(selectedValues.selectedSender)?'':selectedValues.selectedSender);
		selectOperationIncomeType.val(Util.isNullOrEmpty(selectedValues.selectedIncomeType)?'':selectedValues.selectedIncomeType);
		selectOperationNeedinessLevel.val(Util.isNullOrEmpty(selectedValues.selectedNeedinessLevel)?'':selectedValues.selectedNeedinessLevel);
	}
	else {
		selectOperationStatus.val('ROGZITETT');
	}
	

	
	//selectCustomerStatus.val(selectedStatus);
	//selectQualification.val(selectedQualification);
}

function setOperationTableFormat(){
	var defaultLength = 10;
	if(typeof(Storage) !== "undefined") {
		if (!isNaN(localStorage.operation_list_default_page_length) && (localStorage.operation_list_default_page_length != "")){
			defaultLength = localStorage.operation_list_default_page_length;
		}
	}
	
	$('#operation-table').DataTable(Util.getDefaultDataTable (defaultLength));
	
	$('#operation-table_length').css('margin-bottom',  '10px');	
	$('select[name="operation-table_length"]').change(function(){
		if(typeof(Storage) !== "undefined") {
			localStorage.setItem("operation_list_default_page_length", $(this).val());
		}
	});
	
	$('#operation-table').show();
	
}

function openOpertaionDetail(id){
	$('#operation-selected-id').val(id);
	var operationDetailTemplate = _.template($('#template-operation-detail-general').html());
	
	operationData = {id: null, 
					 status: null,
					 has_transport: null,
					 operation_type: getSiteType(),
					 is_wait_callback: null, 
					 customer_id: null, 
					 description: null, 
					 neediness_level: null, 
					 sender: null,
					 income_type: null,
					 income: null, 
					 others_income: null,
					 created_info: null, 
					 modified_info: null,
					 operationDetails: []
					 };
	$('#operation').hide();
	if (id == 0){
		//Új
		$('#operation-detail-general').html(operationDetailTemplate(operationData));
		initNumericFields();
		initOperationSelectElements();
		initFormEditabled();
		
		$('#tr-operation-detail-id').hide();
		$('#tr-operation-detail-customer-address').hide();
		$('#tr-operation-detail-created').hide();
		$('#tr-operation-detail-modified').hide();
		$('#href-operation-detail-attachment').hide();
	}
	else {
		//Módosítás
		var url = OPERATION_URL_LIST_REFRESH;
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
		    	
		    	operationData = data[0];
		    	$('#operation-detail-general').html(operationDetailTemplate(operationData));
		    	reloadOperationDetailsTable();
		    	initNumericFields(); 
		    	initOperationSelectElements({
					selectedOperationStatus: operationData.status,
					selectedSender: operationData.sender,
					selectedIncomeType: operationData.income_type,
					selectedNeedinessLevel: operationData.neediness_level
				});
		    	selectCustomer(operationData.customer_id, operationData.customer_format, operationData.full_address_format);
		    	
		    	initFormEditabled();
				$('#tr-operation-detail-id').show();
				$('#tr-operation-detail-customer-address').show();
				$('#tr-operation-detail-created').show();
				$('#tr-operation-detail-modified').show();
				$('#href-operation-detail-attachment').show();
				$('#operation-detail-attachement-div').show();
				refreshOperationDetailAttachment(id);
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
	}
	
	$('#operation-detail-tabs').tabs();
	$('#operation-detail').show();
	
	if (getSiteType() == 'KERVENYEZES'){
		$('#tr-element-dialog-upload').hide();
	}
	else {
		$('#tr-element-dialog-upload').show();
	}
	
}

function initFormEditabled(){
	if ($('#operation-form-isEditable').val() == 'true'){
		$('#operation-detail-save').show();
		$('#operation-detail-new-element').show();
		initOperationFormButtons();
	}
	else {
		$('#operation-detail-save').hide();
		$('#operation-detail-new-element').hide();
		handleOperationDetailCancelClick();
	}
}

function openCustomerDialog(){
	
	document.getElementById("find-customer-type-1").disabled = true;
	document.getElementById("find-customer-type-2").disabled = true;
	if (getSiteType() == "KERVENYEZES"){
		document.getElementById("find-customer-type-2").checked = true;
	}
	else {
		document.getElementById("find-customer-type-1").checked = true;
	}
	$('#refresh-customer-list').trigger('click');
	
	$('#dialog-customer').dialog('open');
}

function selectCustomer(id, fullName, fullAddress){
	$('#dialog-customer').dialog('close');
	operationData.customer_id = id;
	$('#operation-detail-customer-data').text(fullName);
	$('#operation-detail-customer-address').text(fullAddress);
	$('#tr-operation-detail-customer-address').show();
}

function getSiteType(){
	var siteType = $('#operation-type').val();
	if (typeof siteType == 'undefined'){
		return null;
	}
	else {
		return siteType;
	}
}

function reloadOperationData(){
	
	operationData.id = $('#operation-selected-id').val();
	operationData.status =$('#operation-detail-status').val();
	operationData.has_transport = $('#operation-detail-has-transport').is(':checked')?'Y':'N';
	operationData.is_wait_callback = $('#operation-detail-wait-callback').is(':checked')?'Y':'N';
	operationData.description = $.trim($('#operation-detail-description').val());
	operationData.neediness_level = $('#operation-detail-neediness-level').val();
	operationData.sender = $('#operation-detail-sender').val();
	operationData.income_type = $('#operation-detail-income-type').val();
	operationData.income = $('#operation-detail-income').val().split(' ').join('');
	operationData.others_income = $('#operation-detail-others-income').val().split(' ').join('');

}

function checkOperationData(){
	reloadOperationData();
	
	var requiredFields = [{field: 'customer_id', 	local: 'ügyfél'}];
	var errors = Util.checkRequiredFields(requiredFields, operationData);
	
	var errorHtml = '';
	for (var i=0; i<errors.length; i++){
		errorHtml += errors[i] + '<br>';
	}
	
	if (Util.isNullOrEmpty(errorHtml)){
		$('#operation-detail-save-errors').hide();
		return true;
	}
	else {
		$('#operation-detail-save-errors-div').html(errorHtml);
		$('#operation-detail-save-errors').show();
		return false;
	}

}

function checkOtherOperations(){
	
	if (operationData.operation_type == 'FELAJANLAS') {
		saveOperation();
	}
	else {
		var url = OPERATION_URL_LIST_REFRESH;
		url = Util.addUrlParameter(url, 'customer_id', operationData.customer_id);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){
		    			
		    		var lastOperationTime = '2000-01-01 01:01:01';
		    		for (var i=0; i<data.length; i++){
		    			if ((data[i].id != operationData.id) &&
		    				(data[i].operation_type = 'KERVENYEZES') &&
		    				(data[i].status != 'BEFEJEZETT')) {
		    				$('#operation-detail-save-errors-div').html("Az ügyfélnek már van másik folyamatban lévő kérvénye! Kérlek módosísd inkább azt! Kérvényszáma: " + data[i].id);
		    				$('#operation-detail-save-errors').show();
		    				return;
		    			}
		    			
		    			if ((data[i].operation_type == 'KERVENYEZES') && 
		    				(data[i].status == 'BEFEJEZETT') && 
		    				(data[i].created > lastOperationTime)) {
		    				lastOperationTime = data[i].created;
		    			}
		    		}
		    		
		    		var requestLimitDate = new Date($('#server-time').val());
		    		var maxRepeatMonth = $('#operation-max-repeat-month').val();
		    		requestLimitDate.setDate(requestLimitDate.getMonth() - parseInt(maxRepeatMonth, 10));
		    		if ((new Date(lastOperationTime) > requestLimitDate) && (operationData.id == '0')){
		    			///TODO: ezt valami kúlturáltabbra (dialog!!!)
		    			if (confirm('Az ügyfélnek van ' + maxRepeatMonth + ' hónapnál nem régebbi kérvénye! Ennek ellenére folytatód?')){
		    				saveOperation();
		    			}
		    		}
		    		else {
		    			saveOperation();
		    		}
			    },
				error: function(response) {
					Util.handleErrorToConsole(response);
			    }
		});
	}
	
}

function saveOperation(){
	
	var data = JSON.stringify(operationData);
	
	$.ajax({
	    url: OPERATION_URL_SAVE_OPERATION,
	    type: 'POST',
	    data: data,
	    success: function(data){ 
	    	Util.showSaveResultDialog(true, 'Sikeres kérvény/felajánlás mentés!');
	    	openOpertaionDetail(data);
	    },
	    error: function(response) {
	    	Util.handleErrorToDiv(response, $('#operation-detail-save-errors-div'));
	    	$('#operation-detail-save-errors').show();
	    }
	});	
}

function reloadOperationDetailsTable(){
	if (operationData.operationDetails.length > 0){
		var operationDetailsTemplate = _.template($('#template-operation-detail-element-table').html());
		$('#operation-detail-elements').html(operationDetailsTemplate({rows: operationData.operationDetails}));
	}
	else {
		$('#operation-detail-elements').html('Nincs még eleme a kérvénynek!');
	}
	
}

function removeOperationDetailElement(orderIndicator){
	if (confirm('A művelet nem visszavonható! Biztos töröljük véglegesen az elemet?')){
		for (var i=0;i<operationData.operationDetails.length;i++){
			
			if (operationData.operationDetails[i].order_indicator == orderIndicator){
				operationData.operationDetails.splice(i, 1);
			}
			else if (operationData.operationDetails[i].order_indicator < orderIndicator){
				operationData.operationDetails[i].order_indicator--;
			}
		}
		
		reloadOperationDetailsTable();
	}
	
}

function statusChangeOperationDetailElement(orderIndicator){
	if (confirm('A művelet nem visszavonható! Biztos befejzettre állítsuk véglegesen az elemet?')){
		for (var i=0;i<operationData.operationDetails.length;i++){
			if (operationData.operationDetails[i].order_indicator == orderIndicator){
				operationData.operationDetails[i].status = 'BEFEJEZETT';
				operationData.operationDetails[i].status_local = 'Befejezett'; ///TODO: local név a js-ben. Ezt ki kell szedni és szerver oldalról venni
				reloadOperationDetailsTable();
				return;
			}
		}
	}
}

function refreshOperationDetailAttachment(customerId){
	var url = OPERATION_URL_LIST_ATTACHMENT;
	url = Util.addUrlParameter(url, 'id', operationData.id);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){
	    	
	    	if (data.length == 0){
	    		$('#operation-detail-attechments').html('<p>Nem található melléklet!</p>');
	    	}
	    	else {
	    		var operationDetailsAttachmentTemplate = _.template($('#template-operation-detail-attachment-table').html());
		    	$('#operation-detail-attechments').html(operationDetailsAttachmentTemplate({rows:data}));
	    		
	    	}
	    },
		error: function(response) {
			Util.handleErrorToConsole();      
	    }
	});
	
	var operationDetailsTemplate = _.template($('#template-operation-detail-element-table').html());
	$('#operation-detail-elements').html(operationDetailsTemplate({rows: operationData.operationDetails}));
}

function removeOperationDetailAttachment(id){
	
	if (confirm('A művelet nem visszavonható! A fájl végleges törlésre kerül! Biztos folytassuk?')){
		var data = JSON.stringify({id: id});
		
		$.ajax({
		    url: OPERATION_URL_REMOVE_ATTACHMENT,
		    type: 'POST',
		    data: data,
		    success: function(data){ 
		    	Util.showSaveResultDialog(true, 'Sikeres melléklet törlés!');
		    	refreshOperationDetailAttachment(operationData.id);
		    },
		    error: function(response) {
		    	Util.handleErrorToConsole(response);
		    	Util.showSaveResultDialog(false, '(' + response.status + ') ' + response.responseText);
		    }
		});
	}
}

function downloadOperationDetailAttachment(id){
	var url = OPERATION_URL_DOWNLOAD_ATTACHMENT;
	url = Util.addUrlParameter(url, 'file_id', id);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	window.location=url;
}