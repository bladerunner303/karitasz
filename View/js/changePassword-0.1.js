$( document ).ready(function() {
	
	$('#sendChangePassword').click(function(){
		
		var oldPassword = $('#oldPassword').val();
		var newPassword = $('#newPassword').val();
		var newPasswordAgain = $('#newPasswordAgain').val();
		var minPasswordLength = parseInt($('#minPwdLength').val(), 10);
		var result = '';
		
		if (Util.isNullOrEmpty(oldPassword)){
			result = "Adj meg régi jelszót!";
		}
		else if (newPassword != newPasswordAgain) {
			result = "Nem egyezik a két új jelszó!";
		}
		else if (Util.isNullOrEmpty(minPasswordLength)){
			minPasswordLength = 4;
		}
		
		if (newPassword < minPasswordLength){
			result = "Az új jelszónak minimum "+ minPasswordLength + " karakter hosszúnak kell lennie!";
		}
		
		if (Util.isNullOrEmpty(result)){
			
			$('#result').text('');
			
			var data = JSON.stringify({
	             'oldPassword': oldPassword
	            ,'newPassword': newPassword
	        });

			$.ajax({
			    url: "../Controls/changePassword.php",
			    type: 'POST',
			    data: data,
			    success: function(data){ 
			    	$('#oldPassword').val('');
					$('#newPassword').val('');
					$('#newPasswordAgain').val('');
	    			Util.showSaveResultDialog(true, 'Sikeres jelszó módosítás történt!');
			    },
			    error: function(response) {
			        if (response.status == 401){
			        	window.location.replace('login.php');  
			        }
			        else if(response.status == 500){
			        	$('#result').text(response.responseText);
			        }
			    }
			});
		}
		else {
			$('#result').text(result);
		}	
	});	
	
});
