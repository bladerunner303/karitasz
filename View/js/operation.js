
//global constans
var OPERATION_URL_LIST_CODES = '../Controls/listCodes.php?codeTypes=operation_status;neediness_level;sender;income_type&x=' + new Date().getTime().toString();
var OPERATION_URL_LIST_CODE_GOOD_TYPES = '../Controls/listCodes.php?codeTypes=goods_type&x=' + new Date().getTime().toString();
var OPERATION_URL_LIST_REFRESH = '../Controls/listOperations.php';
var OPERATION_URL_UPLOAD_ATTACHEMENT = '../Controls/uploadOperationAttachment.php';
var OPERATION_URL_LIST_ATTACHMENT = '../Controls/listOperationFiles.php';
var OPERATION_URL_REMOVE_ATTACHMENT = '../Controls/removeOperationAttachment.php';
var OPERATION_URL_DOWNLOAD_ATTACHMENT = '../Controls/downloadFile.php';
var OPERATION_URL_SAVE_OPERATION = '../Controls/saveOperation.php';
var OPERATION_URL_NEW_ELEMENT_TYPE = '../Controls/saveCode.php';
var OPERATION_URL_LIST_TRANSPORTS_REFRESH = '../Controls/listOperationTransports.php';

//global variables
var operationDataTable;
var operationData = {};
var operationDataDefaultHash;
var potentialOperationDialogCaller = '';
var potentialOperationCallerOrderIndicator = '';

$( document ).ready(function() {
	initNumericFields();
	handleRefreshOperationListClick();
	handleAddOperationClick();
	handleExportOperationClick();
	initOperationDetailAddElementEvents();
	handleOperationDetailUploadclick();
	$('#refresh-operation-list').trigger('click');
	initOperationDialogs();
	getOperationSelectItems();
	initSelectGoodsType();
	$('#operation-detail-add-element-related-detail').val('');
	$('#operation-detail-add-element-related-detail-format').val('');
	$('#operation-detail-add-element-type-number').val(1);
	$('#operation-dialogs').show();
});

function handleRefreshOperationListClick(){
	$('#refresh-operation-list').click(function(){
		
		var url = OPERATION_URL_LIST_REFRESH;
		
		if (getSiteType() == null) {
			url = Util.addUrlParameter(url, 'customer_id', Util.nvl($('#customer-selected-id').val(), '')); //Beágyazó oldalról származó field
		}
		else {
			url = Util.addUrlParameter(url, 'text', $('#find-operation-text').val());
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
	

	$('#operation').keydown(function (e) {
		  if (e.keyCode == 13) {
			  $('#refresh-operation-list').trigger('click');
		  }
	});
}

function handleAddOperationClick(){
	$('#add-operation').click(function(){
		openOpertaionDetail(0);
	});
}

function handleExportOperationClick(){
	$('#export-operation').click(function(){
		Util.exportHtmlTableToCsv((getSiteType() == "KERVENYEZES")? 'Kérvények' : 'Felajánlások', 'operation-table');
	});
}

function handleOperationCustomerFindClick(){
	$('#operation-detail-customer-find').click(function(){
		openCustomerDialog();
	});
}

function handleOperationDetailCancelClick(){
	$('#operation-detail-cancel').click(function(){
		var goCancel = true;
	//	if (operationDataDefaultHash != (Util.getElementHash('#operation-detail') + Util.getObjectArrayHash(operationData.operationDetails))){
		if (operationDataDefaultHash != Util.getElementHash('#operation-detail')){
			goCancel = confirm('A kilépéssel a mentetlen módosítások elvesznek! Biztos folytatjuk?');
		}
		if (goCancel){
			$('#operation-detail').hide();
			$('#operation').show();
			$('#refresh-operation-list').trigger('click');
			
		}
		
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
		$('#operation-detail-add-element-element-pics-id-list').val('');
		$('#tr-element-pics-list-div').html('');
		removeOperationDetailElementSelectedPotentialElement();
		potentialOperationDialogCaller = 'function-new-element-click';
		$('#dialog-add-element').dialog('open');
	});
}

function handleOperationDetailAddElementSaveClick(){
	$('#operation-detail-add-element-save').click(function(){

		var elementName = $.trim($('#operation-detail-add-element-name').val());
		var elementType = $('#operation-detail-add-element-type').val();
		var elementTypeName = $('#operation-detail-add-element-type option:selected').text();
		var elementNumber = parseInt($('#operation-detail-add-element-type-number').val(),10);
		
		var detailIds = $('#operation-detail-add-element-related-detail').val().split(';');
		var relatedOperationDetails = $('#operation-detail-add-element-related-detail-format').val().split(';');
		var detailFiles = [];
		if (!Util.isNullOrEmpty($('#operation-detail-add-element-element-pics-id-list').val())){
			detailFiles = $('#operation-detail-add-element-element-pics-id-list').val().split(';');
		}
		
		for (var i=0;i<elementNumber; i++){
		
			var detailId = Util.nvl(detailIds[i], '');
			var relatedOperationDetail = Util.nvl(relatedOperationDetails[i], '');
			
			operationData.operationDetails.push({
				name: elementName,
				goods_type: elementType,
				goods_type_local: elementTypeName,
				status: 'ROGZITETT',
				status_local: 'Rögzített',
				order_indicator: operationData.operationDetails.length,
				detail_id: detailId,
				related_operation_detail: relatedOperationDetail,
				id: null,
				detail_files: detailFiles
			});	
		}
		
		$('#operation-detail-add-element-cancel').trigger('click');
		reloadOperationDetailsTable();
	});
}

function handleOperationDetailAddElementCancelClick(){
	$('#operation-detail-add-element-cancel').click(function(){
		$('#operation-detail-add-element-related-detail').val('');
		$('#operation-detail-add-element-related-detail-format').val('');
		$('#operation-detail-add-element-name').val('');
		$('#dialog-add-element').dialog('close');
	});
}

function handleOperationDetailUploadclick(){
	$("#operation-upload").click(function(){
		var fileData = $('#operation-userfile').prop('files')[0];  
		if (Util.isNullOrEmpty(fileData)){
			Util.showSaveResultDialog(false, 'Nem választottál ki fájlt');
			return;
		}
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

function handleOperationDetailNewElementTypeClick(){
	$('#operation-detail-new-element-type').click(function(){
		
		///TODO: prompt ablak kicserélése
		var newElement = prompt("Ad meg kérlek az új elemet");
		if (newElement != null) {
		    
			if ((newElement.length < 2) || (newElement.length > 18)){
				alert('Nem megfelelő hosszúságú kód! Csak 2 és 18 karakter közötti engedélyezett!');
				return;
			}
			
			var data = JSON.stringify({code_type: 'goods_type', code_value: newElement});
			
			$.ajax({
			    url: OPERATION_URL_NEW_ELEMENT_TYPE,
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

function handleOperationDetailAddElementUploadPicsClick(){
	if (getSiteType() == 'KERVENYEZES'){
		$('#tr-element-dialog-upload').hide();
	}
	else {
		$('#tr-element-dialog-upload').show();
		$('#operation-detail-add-element-upload-pics').click(function(){
			var fileData = $('#operation-detail-add-element-upload').prop('files')[0];
			 
			if (Util.isNullOrEmpty(fileData)){
				alert('Nem választottál ki kép fájlt');
				return;
			}
			$('#operation-detail-add-element-upload').val('');
		    var formData = new FormData();                  
		    formData.append('userfile', fileData);               
		    var url = OPERATION_URL_UPLOAD_ATTACHEMENT;
		    $.ajax({
		                url: url, 
		                dataType: 'text', 
		                cache: false,
		                contentType: false,
		                processData: false,
		                data: formData,                         
		                type: 'post',
		                success: function(data){
		                	
		                	var picsId = $('#operation-detail-add-element-element-pics-id-list').val();
		                	if (!Util.isNullOrEmpty(picsId)){
		                		picsId += ';';
		                	}
		                	
		                	$('#operation-detail-add-element-element-pics-id-list').val(picsId + JSON.parse(data));	
		                	$('#tr-element-pics-list-div').append('<p>' + fileData.name + '</p>');
		                   
		                	$('#tr-element-pics-list').show();
		                	
		                },
		        		error: function(response) {
		        			Util.handleErrorToConsole(response);
		        			Util.showSaveResultDialog(false, response.responseText);
		        	    }
		     });
		});

	}

}

function handleOperationDetailAddElementTypeNumberChange(){
	$('#operation-detail-add-element-type-number').change(function(){
		var currentValue = parseInt($(this).val(),10);
		var selectedElements = $('#operation-detail-add-element-related-detail').val().split(';');
		
		if (currentValue < selectedElements.length){
			selectedElements.splice(currentValue);
			$('#operation-detail-add-element-related-detail').val(selectedElements.join(';'));
			$('#operation-detail-add-element-related-detail-format').val().split(';').splice(currentValue).join(';');
			
		}
	
	});
}

function handleOperationDetailAddElementTypeSelectChange(){
	$('#operation-detail-add-element-type').change(function(){
		
		if (Util.isNullOrEmpty($(this).val())){
			$('#operation-detail-add-element-potential-element').html('');
			return;
		}
		
		listPotentionalOperations($(this).val(), $('#operation-detail-add-element-potential-element'));
		
	});
}

function getOperationSelectItems(){
	$.ajax({
	    url: OPERATION_URL_LIST_CODES,
	    type: 'GET',
	    success: function(data){ 
	    	operationStatus = data.operation_status;
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
	initPotentionalOperationsDialog();
	initOperationSlideShowDialog();
}

function initAddElementDialog(){
	$('#dialog-add-element').dialog(Util.getDefaultDialog());
}

function initCustomerDialog(){
	$('#dialog-customer').dialog(Util.getDefaultDialog());
}

function initPotentionalOperationsDialog(){
	$('#dialog-potentional-operations').dialog( Util.getDefaultDialog());
}

function initOperationSlideShowDialog(){
	$('#dialog-operation-slideshow').dialog(Util.getDefaultDialog());
}

function initOperationFormButtons(){
	
	handleOperationDetailSaveClick();
	handleOperationDetailNewElementClick();
	handleOperationCustomerFindClick();
	handleOperationDetailCancelClick();
	

}

function initOperationDetailAddElementEvents(){
	handleOperationDetailAddElementSaveClick();
	handleOperationDetailAddElementCancelClick();
	handleOperationDetailNewElementTypeClick();
	handleOperationDetailAddElementTypeSelectChange();
	handleOperationDetailAddElementUploadPicsClick();
}

function initOperationSelectElements(selectedValues) {
	
	var selectFinderStatus = $('#find-operation-status');
	var selectOperationStatus = $('#operation-detail-status');
	var selectOperationSender = $('#operation-detail-sender');
	var selectOperationIncomeType = $('#operation-detail-income-type');
	var selectOperationNeedinessLevel  = $('#operation-detail-neediness-level');
	
	selectFinderStatus.append($('<option></option>').val('').html(' '));
	selectOperationSender.append($('<option></option>').val('').html(' '));
	selectOperationIncomeType.append($('<option></option>').val('').html(' '));
	selectOperationNeedinessLevel.append($('<option></option>').val('').html(' '));
	
	for(var i=0; i< operationStatus.length; i++){
		selectFinderStatus.append($('<option></option>').val(operationStatus[i].id).html(operationStatus[i].code_value));
		selectOperationStatus.append($('<option></option>').val(operationStatus[i].id).html(operationStatus[i].code_value));
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
}

function initSelectGoodsType(selectedValue){
	$.ajax({
	    url: OPERATION_URL_LIST_CODE_GOOD_TYPES,
	    type: 'GET',
	    success: function(data){
	    	goodsTypes = data.goods_type;
	    	var selectGoodsType = $('#operation-detail-add-element-type');
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
					 last_status_changed_info: null,
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
		$('#tr-operation-detail-last-status-changed').hide();
		$('#href-operation-detail-attachment').hide();
		refreshOperationTransports(id);		
		//operationDataDefaultHash = Util.getElementHash('#operation-detail') + Util.getObjectArrayHash(operationData.operationDetails);
		operationDataDefaultHash = Util.getElementHash('#operation-detail');
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
				$('#tr-operation-detail-last-status-changed').show();
				$('#href-operation-detail-attachment').show();
				$('#operation-detail-attachement-div').show();
				refreshOperationDetailAttachment(id);
				refreshOperationTransports(id);
		//		operationDataDefaultHash = Util.getElementHash('#operation-detail') + Util.getObjectArrayHash(operationData.operationDetails);
				operationDataDefaultHash = Util.getElementHash('#operation-detail');
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
	}
	
	$('#operation-detail-tabs').tabs();
	$('#operation-detail').show();
	
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
		    				(data[i].transport_date > lastOperationTime)) {
		    				lastOperationTime = data[i].transport_date;
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
		$('#operation-detail-elements').html('<p>Nincs még mentett eleme a kérvénynek/felajánlásnak!</p>');
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
	if (confirm('A művelet nem visszavonható! Biztos befejezettre állítsuk véglegesen az elemet?')){
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

function removeOperationDetailRelatedElement(orderIndicator){
	if (confirm('A művelet nem visszavonható! Biztos töröljük véglegesen az elemet?')){
		for (var i=0;i<operationData.operationDetails.length;i++){
			
			if (operationData.operationDetails[i].order_indicator == orderIndicator){
				operationData.operationDetails[i].detail_id = null;
				operationData.operationDetails[i].related_operation_detail = null;
				return;
			}
		}
		
		reloadOperationDetailsTable();
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

function selectPotentialOperations(operationDetailId,  customer, operation, detailName){
	
	if (potentialOperationDialogCaller === 'function-showPotentialOperations'){
		//Ha az elem táblázatból hívják
		operationData.operationDetails[potentialOperationCallerOrderIndicator].detail_id = operationDetailId;
		operationData.operationDetails[potentialOperationCallerOrderIndicator].related_operation_detail = operation + '/' + customer + ' ' + detailName;
		reloadOperationDetailsTable();
		$('#dialog-potentional-operations').dialog('close');
	}
	else {
		//Ha felvitel ablakból hívják
		var relatedDetail = $('#operation-detail-add-element-related-detail').val();
		if (relatedDetail.split(';').indexOf(operationDetailId) !== -1){
			alert('Az adott felajánlás/kérvény már kiválasztásra került!');
			return;
		}
		
		if (relatedDetail.split(';').length <= parseInt($('#operation-detail-add-element-type-number').val(), 10)){
			$('#operation-detail-add-element-related-detail').val(relatedDetail + operationDetailId + ';');
			$('#operation-detail-add-element-related-detail-format').val($('#operation-detail-add-element-related-detail-format').val() 
					+ operation + '/' + customer + ' ' + detailName + ';');
			
			var html = '<fieldset id="operation-detail-element-fieldset-' + operationDetailId + '"><legend>Kapcsolt elem</legend>';
			html += '<p>Ügyfél: ' + customer + '</p>';
			html += '<p>Kérvény/felajánlás: ' + operation + ' ' + detailName + '</p>';
			html += '<div class="icon-cancel-mid-little" onclick="removeOperationDetailElementSelectedPotentialElement(\'' + operationDetailId + '\');" title="Kapcsolt elem törlése"></div>';
			html += '</fieldset>';
			
			$('#operation-detail-add-element-related-operation').html($('#operation-detail-add-element-related-operation').html() +html);
			$('#tr-element-dialog-related-operation').show();
		}
		else {
			alert('Nem választhatsz ki többet, mert elérted a mennyiség mezőben megadott számot!');
			return;
		}
	}
	
}

function showPotentialOperations(goodsType, orderIndicator){
	listPotentionalOperations(goodsType, $('#operation-potential-operations'));
	potentialOperationDialogCaller = 'function-showPotentialOperations';
	potentialOperationCallerOrderIndicator = orderIndicator;
	$('#dialog-potentional-operations').dialog('open');
	
}

function listPotentionalOperations(detail, resultsDiv){
	var url = OPERATION_URL_LIST_REFRESH;
	url = Util.addUrlParameter(url, 'operation_type', getSiteType()=='KERVENYEZES'?'FELAJANLAS':'KERVENYEZES');	
	url = Util.addUrlParameter(url, 'detail', detail);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){ 
	    	if (data.length > 0){
	    		var potentialOperationTableTemplate = _.template($('#template-operation-detail-potential-operations').html());
		    	resultsDiv.html(	potentialOperationTableTemplate({rows: data}));
	    	}
	    	else {
	    		resultsDiv.html('<p>Nem található javasolt kérvény/felajánlás</p>');
	    	}
	    	
	    },
		error: function(response) {
			Util.handleErrorToConsole(response);
	    }
	});
}

function removeOperationDetailElementSelectedPotentialElement(operationDetailId){
	
	var selectedItems = $('#operation-detail-add-element-related-detail').val().split(';');
	var selectedFormats = $('#operation-detail-add-element-related-detail-format').val().split(';');
	
	for (var i=0;i<selectedItems.length; i++){
		if (selectedItems[i] === operationDetailId){
			selectedItems.splice(i, 1);
			selectedFormats.splice(i,1);
			$('#operation-detail-add-element-related-detail').val(selectedItems.join(';'));
			$('#operation-detail-add-element-related-detail-format').val(selectedFormats.join(';'));
			$('#operation-detail-element-fieldset-' + operationDetailId).remove();
			return;
		}
	}
	
}

function refreshOperationTransports(operationId){
	
	if (operationId == 0){
		$('#operation-detail-transport').html('');
	}
	else {
		var url = OPERATION_URL_LIST_TRANSPORTS_REFRESH;
		url = Util.addUrlParameter(url, 'id', operationId);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	if (data.transports.length > 0){
		    		var refreshOperationTransportsTableTemplate = _.template($('#template-operation-transport-table').html());
			    	$('#operation-detail-transport').html(	refreshOperationTransportsTableTemplate({rows: data.transports}));
		    	}
		    	else {
		    		$('#operation-detail-transport').html('<p>Nem található szállítás esemény a kérvényhez/felajánláshoz</p>');
		    	}
		    	
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		    }
		});
	}
	
}

function openPictures(orderIndicator){
	for (var i=0;i<operationData.operationDetails.length; i++){
		if ((operationData.operationDetails[i].order_indicator) == orderIndicator){
			var detailFiles = operationData.operationDetails[i].detail_files;
			if (detailFiles.length == 0){
				Util.showSaveResultDialog(false, 'Nem található kép az elemhez');
				return;
			}
			else {
				var data = [];
				for (var n=0; n<detailFiles.length; n++){
					var url = OPERATION_URL_DOWNLOAD_ATTACHMENT;
					url = Util.addUrlParameter(url, 'file_id', detailFiles[n]);
					url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
					data.push({index: n, src: url });
				}
				var operationSlideShowTemplate = _.template($('#template-operation-slideshow').html());
				$('#dialog-operation-slideshow-mySlides').html(operationSlideShowTemplate({rows:data}));
				$('#dialog-operation-slideshow').dialog('open');
				showSlides(1);
			}
		}
	}
}
