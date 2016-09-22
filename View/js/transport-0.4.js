//global constanst
var TRANSPORT_URL_REFRESH = '../Controls/listTransports.php';

//global variables
var transportDataTable;

$( document ).ready(function() {
	
	initDatePickerFields();
	handleRefreshTransportListClick();
	handleAddTransportClick();
	initTransportAddressDialog();
	initTransportAddressAddDialog();
	
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

function handleAddTransportClick(){
	$('#add-transport').click(function(){
		openTransportDetail(0);
	});
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
	var transportData = {id: null, //'<script type="text/javascript">var x="v"; alert("xss");</script>', 
						status: null,
						createdInfo: null, 
						modifiedInfo: null};
	$('#transport').hide();
	if (id == 0){
		//Új
		$('#transport-detail-general').html(transportDetailTemplate(transportData));
		$('#tr-created').hide();
		$('#tr-modified').hide();
	}
	else {
		//Módosítás
		$('#tr-created').show();
		$('#tr-modified').show();
	}
	$('#transport-detail-general').show();
	
	$('#transport-cancel').click(function(){
		$('#transport-detail-general').hide();
		$('#transport').show();
		$('#refresh-transport-list').trigger('click');
		
	});

}

function openAddress(id){
	$('#dialog-transport-address-table').DataTable(Util.getDefaultDataTable (10));
	$('#dialog-transport-address').dialog('open');
}

function addAddress(id){
	$('#dialog-transport-address-add').dialog('open');
	$('#transport-date').datepicker();
	$('#transport-detail-address-table').html($('#dialog-transport-address-table').html());
	$('#dialog-transport-address-table').DataTable(Util.getDefaultDataTable (10));
}
	



