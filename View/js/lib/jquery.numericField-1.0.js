/*!
	 * jQuery numericField Plugin v1.0 
	 * http://ats-sys.hu
	 *
	 * Copyright 2014 ATS-SYS Software Kft. Lacz Levente
	 * Released under ATS-SYS license. Please write more info: info@ats-sys.hu
	 * 
	 *  ## Használat szakasz ## 
		$( "#numberField" ).numericField(true); 
		
		$( "#numberFieldWithoutThousend" ).numericField(false);	
	
		## Előfeltételek (tesztelve): ##
		Javascript oldalon:  
		- Keretrendszer: jquery 1.11.0
	
	 */

(function($) {	
				
	$.fn.numericField = function(hasThousendSeparator) {
		
		//thousendSeparator : true;
		
		$(this).keypress(function(event){			
			return _keypress(event);
		});
		
		$(this).keyup(function(event){
			return _keyup(this);
		});
				
		$(this).on('paste', function(e){
			return false;
		});		
		
		function _keyup(obj){
			
			if (hasThousendSeparator) {
				var ns = '';
				var a = obj.value;
				a = a.replace(/ /g, '');
				//tizedes vessző kezelése
				var n = a.indexOf('.');var tizedesResz = '';
				if (n != -1) {
					tizedesResz = a.substring(n); 
					a = a.substring(0, n); }
				
				i = a.length;
				for (j = 3; j <= i + 2; j += 3) { 
					if (ns == "") {
						ns = a.substring(i - j, i - j + 3); 
					} 
					else { 
						ns = a.substring(i - j, i - j + 3) + " " + ns; 
					} 
				}
				
				obj.value = ns + tizedesResz;
			}
		}
		
		function _keypress(evt){
				
			var charCode = (evt.which) ? evt.which : evt.keyCode;
			if (charCode > 31 && (charCode < 48 || charCode > 57)) {
				if (charCode == 46) { 
					return true; 
				} 
				else { 
					return false; 
				}  
			}
			return true;
			
			
		}
      
   }		  

})(jQuery);