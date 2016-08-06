$( document ).ready(function() {

	$('#refresh-transport-list').click(function(){
		var id = $('#transport-selected-id').val();
		var transportTableTemplate = _.template($('#template-transport-table').html());
		///TODO: mockot kicserélni
		var rows = [];
		rows.push ({id: 1000,
					status: 'Befejezett', 
					transportDate: '2016.06.01',
					createdInfo: '2016.06.01. 14:12:45 (Jerne)',
					modifiedInfo: '2016.06.04. 15:12:25 (Jerne)'
				});
		rows.push({
					id: 1002,
					status: 'Folyamatban', 
					transportDate: '2016.06.02',
					createdInfo: '2016.06.04. 14:12:45 (Jerne)',
					modifiedInfo: null
				});	
		
		$('#transport-table > tbody').html(
				transportTableTemplate({rows: rows})
		);
		
		setTransportTableFormat();
	});
	
	$('#add-transport').click(function(){
		openTransportDetail(0);
	});
	
	initTransportAddressDialog();
	initTransportAddressAddDialog();
	$('#transport-date').datepicker();
});

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
	



