statuses = [];
operationStatus = [];
goodsTypes = [];
senders = [];
incomeTypes = [];
needinesLevels = [];
customerQualifications = [];

$( document ).ready(function() {

	$("#logout").click(function(){
		var data = null;
		$.post("../Controls/logout.php", data, function(returnedData) {
			window.location.replace('login.php');
		});
		return false;
	});

});

Util = (function(){
	return {
		nvl : function (myObject, defaultIsNull) {
			if ((myObject === null) || (typeof myObject == 'undefined')){
				return defaultIsNull;
			}
			else {
				return myObject;
			}
		},
		isNullOrEmpty : function (myObject){
			if (Util.nvl(myObject, '') !== '') {
				return false;
			}
			else {
				return true;
			} 
		},
		lpad : function (str, max, character) {
			  str = str.toString();
			  return str.length < max ? Util.lpad(character + str, max, character) : str;
		},
		checkRequiredFields : function (requiredFields, sourceObject){
			var errors = [];
			for (var i=0;i<requiredFields.length; i++){
				if (Util.isNullOrEmpty(sourceObject[requiredFields[i].field])){
					errors.push(requiredFields[i].local + ' mező nem lehet üres!');
				}
			}
			return errors;
		},
		logConsole : function (message){
			if (typeof console.log == 'function'){ 
				console.log(message);
			}
		},
		handleErrorToConsole : function (response){
			 if (response.status == 401){
		        	window.location.replace('bejelentkezes.php');  
		        }
		        else if(response.status == 500){
		        	Util.logConsole(response.responseText);
		        }
		},
		handleErrorToDiv : function (response, errorDiv){
			if (response.status == 401){
	        	window.location.replace('bejelentkezes.php');  
	        }
	        else if(response.status == 500){
	        	// errorDiv.css("background-color", "red");
	        	var errors = response.responseText.split(";");
	        	var errorText = '';
	        	for (var i=0; i<errors.length; i++){
	        		errorText += errors[i] + '\r\n';
	        	}
	        	errorDiv.text(errorText);
	        }
		},
		addUrlParameter : function (url, parameterName, parameterValue){
			if (!Util.isNullOrEmpty(parameterValue)){
				if (url.indexOf('?') === -1){
					url += '?';
				}
				else if (url[url.length] != '&') {
					  url += '&';
				}
				url += parameterName + "=" + parameterValue; 
			}
			return url;
		},
		showSaveResultDialog : function (isSuccessful, alternativeMessage){
			$('#saveDialog').remove();
			var html = '<div id="saveDialog" style="display: none">' +
						((isSuccessful) ? '<img src="images/nike.png"/>' : '<img src="images/error.png"/>') + 
						((isSuccessful) ? 
								(typeof alternativeMessage == 'undefined') ? '<span>Sikeres mentés</span>' : alternativeMessage
										:
								((typeof alternativeMessage == 'undefined') ? '<span>Sikertelen mentés</span>' : alternativeMessage)
											+ '<br><button id="saveDialogClose">Bezár</button>'
						)		
						+ '</div>';

			if ($('#saveDialog').length == 0) {
				$('body').append(html);
			}			

			if (( typeof alternativeMessage == 'undefined' ) && (isSuccessful)){			
				$('#saveDialog').dialog({autoOpen: false, hide: {effect: "fadeOut", duration: 1500},  width: '200', height: '75'} );
			}
			else if (( typeof alternativeMessage != 'undefined' ) && (isSuccessful)){
				$('#saveDialog').dialog({autoOpen: false, hide: {effect: "fadeOut", duration: 1500},width: 'auto', height: 'auto'});
			}
			else if (( typeof alternativeMessage != 'undefined' ) && (!isSuccessful)){
				$('#saveDialog').dialog({autoOpen: false,  width: 'auto', height: 'auto'});
			}
			else {
				$('#saveDialog').dialog({autoOpen: false, width: '200', height: '75'});
			}
			$('#saveDialog').parent().find(".ui-dialog-titlebar").hide();
			$('#saveDialog').dialog('open');
			if (isSuccessful){
				$('#saveDialog').dialog('close');
			}
			else {
				$('#saveDialogClose').click(function(){
					$('#saveDialog').dialog('close');
				});
			}
		},
		getDefaultDataTable : function (defaultLength){
			
			return	{
				"retrieve": true,
		        "searching":   false,
		        "iDisplayLength" : defaultLength,
		        "language": {
			        "lengthMenu": "Találatok _MENU_ db/oldal <br>",
		            "zeroRecords": "Nincs találat",
		            "info": " _PAGE_ / _PAGES_",
		            "infoEmpty": "Nincs találat",
		            "infoFiltered": "(filtered from _MAX_ total records)",
		            "paginate": 
		            {
						"next": "Következő",
						"previous": "Előző"
		             }
		        }
		    };
			
		},
		getDefaultDialog : function(){
			return {
				 autoOpen: false, 
				 modal: true,
				 width: 'auto'
			};
		},
		clearDataTable : function (dataTable){
			if (dataTable != null){
				dataTable.clear();
				dataTable.destroy();				
			}
		}
	};
}());

	



