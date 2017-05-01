//global constanst
var TRANSPORT_URL_REFRESH = '../Controls/listTransports.php';
var TRANSPORT_SET_TRANSPPORT_INFO = '../Controls/setTransportInfo.php';

$( document ).ready(function() {
	
	handleRefreshAddressListClick();
	$('#refresh-address-list').trigger('click');
});

function handleRefreshAddressListClick(){
	$('#refresh-address-list').click(function(){
		var today = $('#server-time').val().substring(0,10);
		//var today = '2016-09-15';
		var url = TRANSPORT_URL_REFRESH;
		url = Util.addUrlParameter(url, 'begin_date', today);
		url = Util.addUrlParameter(url, 'end_date', today);
		url = Util.addUrlParameter(url, 'limit', 1);
		url = Util.addUrlParameter(url, 'x', new Date().getTime().toString());
		
		$.ajax({
		    url: url,
		    type: 'GET',
		    success: function(data){ 
		    	var dailyData = data[0];
		    	if (typeof dailyData == 'undefined'){
		    		$('#tr-daily-transport').html('<p>Nem található a mai napra szállítás!</p>');
		    	}
		    	else {
		    		
		    		for (var i=0; i<dailyData.addresses.length; i++){
		    			dailyData.addresses[i].itemsFormat = '';
		    			for (var n=0; n<dailyData.addresses[i].items.length; n++){
		    				dailyData.addresses[i].itemsFormat += dailyData.addresses[i].items[n].name_format + '\r\n';
		    			}
		    		}
		    		
		    		var transportTableTemplate = _.template($('#template-transport-table').html());
			    	$('#tr-daily-transport').html(	transportTableTemplate({rows: dailyData.addresses}) );
		    	}
		    		
		    },
			error: function(response) {
				Util.handleErrorToConsole(response);
		    }
		});
		
	});
}

function addDescription(id){

	var description = prompt('Megjegyzés hozzáadása');
	
	if (!Util.isNullOrEmpty(description)){
	
		var data = {};
		data.id = id;
		data.description = description;
		data.isSetSuccessful = false;
		data.isSetCanceled = false;
		data = JSON.stringify(data);
		
		$.ajax({
		    url: TRANSPORT_SET_TRANSPPORT_INFO,
		    type: 'POST',
		    data: data,
		    success: function(data){ 
		    	$('#refresh-address-list').trigger('click');
		    },
		    error: function(response) {
		    	Util.handleErrorToDiv(response, $('#result'));
		    }
		});
	}
		

}

function setSuccessful(id){
	if (confirm("Biztos sikeresre állítjuk?")){

		var data = {};
		data.id = id;
		data.description = '';
		data.isSetSuccessful = true;
		data.isSetCanceled = false;
		data = JSON.stringify(data);
		
		$.ajax({
		    url: TRANSPORT_SET_TRANSPPORT_INFO,
		    type: 'POST',
		    data: data,
		    success: function(data){ 
		    	$('#refresh-address-list').trigger('click');
		    },
		    error: function(response) {
		    	Util.handleErrorToDiv(response, $('#result'));
		    }
		});	
	}
}

function setCanceledClick(id){
	if (confirm("Biztos sikertelenre állítjuk?")){

		var data = {};
		data.id = id;
		data.description = '';
		data.isSetSuccessful = false;
		data.isSetCanceled = true;
		data = JSON.stringify(data);
		
		$.ajax({
		    url: TRANSPORT_SET_TRANSPPORT_INFO,
		    type: 'POST',
		    data: data,
		    success: function(data){ 
		    	$('#refresh-address-list').trigger('click');
		    },
		    error: function(response) {
		    	Util.handleErrorToDiv(response, $('#result'));
		    }
		});	

	}
}