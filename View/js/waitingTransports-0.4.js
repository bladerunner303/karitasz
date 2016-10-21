//global constans
var WAIT_TRANSPORT_OPERATION_URL_REFRESH = '../Controls/listWaitingTransportOperations.php';
var WAIT_TRANSPORT_ONE_OPERATION_URL = '../Controls/listOperations.php';

//global variables
var waitingTransportOperationTable;

$( document ).ready(function() {
	
	handleRefreshWaitingTransportOperationsListClick();
	initWaitingTransportOperationsDialogs();
	$('#refresh-waiting-transport-operations-list').trigger('click');
});

function initWaitingTransportOperationsDialogs(){
	$('#dialog-transports-items').dialog({
			 autoOpen: false, 
			 modal: true,
			 width: 'auto'
	});
}

function handleRefreshWaitingTransportOperationsListClick(){
	
	$('#refresh-waiting-transport-operations-list').click(function(){
		var url = WAIT_TRANSPORT_OPERATION_URL_REFRESH;
		url = Util.addUrlParameter(url, 'text', $('#find-waiting-transport-operations-text').val());
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	Util.clearDataTable(waitingTransportOperationTable);
		    	if (data.length === 0){
		    		$('#waiting-transport-operations-table').html('<p>Nem található találat</p>');
		    	}
		    	else {
		    		var waitingTransportOperationsTableTemplate = _.template($('#template-waiting-transport-operations-table').html());
			    	$('#waiting-transport-operations-table').html(	waitingTransportOperationsTableTemplate({rows: data}) );	
			    	$('#waiting-transport-operations-table table').DataTable(Util.getDefaultDataTable (100));
		    	}
		    		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		       
		    }
		});
		
	});
}

function openTransportOperationItems(id){
	var url = WAIT_TRANSPORT_ONE_OPERATION_URL;
	url = Util.addUrlParameter(url, 'id', id);
	url = Util.addUrlParameter(url, 'limit', 1);
	url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
	
	$.ajax({
	    url: url,
	    type: 'GET',
	    success: function(data){ 
	    	showOperationItemsTable(data[0].operationDetails);
	    },
		error: function(response) {
			Util.handleErrorToConsole(response);
	    }
	});
}

function showOperationItemsTable(operationDetails){
	var operationItemsTableTemplate = _.template($('#template-transport-operations-items-table').html());
	$('#waiting-transports-transport-items').html(	operationItemsTableTemplate({rows: operationDetails}));
	$('#dialog-transports-items').dialog('open');
}