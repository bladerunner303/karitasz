
    <?php
    	$type = empty($_GET["type"])?null:$_GET["type"];
    	$_GET["site"] = 'customer';
		require_once 'header.php';
	?>
	
		<input type="hidden" id="customer-selected-id" value="" />
		<input type="hidden" id="customer-page-type" value="<?php echo $type; ?>" />
		<input type="hidden" id="valid-phone-number-regexp" value="<?php echo Config::getContextParam("VALID_PHONE_NUMBER_REGEXP_JS"); ?>" />
		
		<div id="customer" class="mainArea">
			<img src="images/users.png" class="titleIcon"/>
			<h2 class="title">Ügyfelek</h2>
			<br>
			<fieldset class="fieldSetSize mainWidth">
		    <legend>feltételek:</legend>
		    	Típus: 
		    	<input type="radio" name="find-customer-type" value="FELAJANLO" id="find-customer-type-1">Felajánló&nbsp;
  				<input type="radio" name="find-customer-type" value="KERVENYEZO" id="find-customer-type-2" checked>Kérvényező &nbsp;
  				<input type="text" id="find-customer-text" class="width500 find">
		  		<br>
		  		
		    </fieldset>
		     <br>
		     <div style="float:left">Találatok maximális száma: 
		  		<input type="text" id="find-customer-result-max" value="<?php echo Config::getContextParam("DEFAULT_RESULT_SIZE"); ?>" class="input-short"/>
		    </div>&nbsp;
		    <div class="icon-refresh" id="refresh-customer-list" title="Frissiti a táblázatot" ></div>
		    <div class="icon-add" id="add-customer" title="Új ügyfél rögzítése"></div>
		    
		    			<br>
			<table id="customer-table" style="display: none">
				<thead>
					<tr>
						<th>Azonosító</th>
						<th>Típus</th>
						<th>Név</th>
						<th>Cím</th>
						<th>Minősítés</th>
						<th>Státusz</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			
		</div>
		
		<div id="customer-detail" class="mainArea" style="display: none">
			<div id="customer-detail-tabs">
			  <ul>
			    <li><a href="#customer-detail-general">Alapadatok</a></li>
			    <li><a href="#customer-detail-operation" id="href-customer-detail-operation">Kérvények/felajánlások</a></li>
			    <li><a href="#customer-detail-log" id="href-customer-detail-log">Adat változások</a></li>
			  </ul>
			    <div id="customer-detail-general"></div>
			    <div id="customer-detail-operation">
			    	<?php require_once 'operation.php';	?>
			    </div>
			    <div id="customer-detail-log"></div>
			</div>
		</div>
		
		<div id="dialog-similar-customers" title="Hasonló ügyfelek">
			<p>A következő ügyfelek hasonlítanak ahhoz amit megadtál. Kérlek vizsgáld meg, hogy nem-e a lentiek valamelyikére gondolsz?</p>
			<table id="table-similar-customer">
				<thead>
					<tr>
						<td>Azonosító</td>
						<td>Név</td>
						<td>Cím</td>
						<td>Telefon</td>
						<td>Minősítés</td>
						<td>Adószám</td>
						<td>TAJ</td>
						<td></td>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			<button id="similar-customer-save">Egyik sem, folytatom a mentést</button>
		</div>
		
		<script type="text/x-custom-template" id="template_customer_table">
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id%></td>
						<td><%-rows[row].customer_type_local%></td>
						<td><%-rows[row].full_name%></td>
						<td><%-rows[row].zip%>&nbsp;<%-rows[row].city%><br><%-rows[row].street%></td>
						<td><%-rows[row].qualificaton_local%></td>
						<td><%-rows[row].status_local%></td>
						<td <?php if ($type=="selector") { echo 'style="width: 64px";';} ?> >
							<div class="icon-edit" onclick="openCustomerDetail('<%-rows[row].id%>');" title="Részletek"></div>
							<?php if ($type=="selector") { 
									echo '<div class="icon-select" onclick="selectCustomer('. "'<%-rows[row].id%>','<%-rows[row].full_name%> (<%-rows[row].id%>)', '<%-rows[row].full_address%>');".'" title="Kiválasztás"></div>'; 
							} ?>
						</td>
					</tr>
			<% } %>
		</script>
		
		<script type="text/x-custom-template" id="template_customer_detail">
		<table>
			<tr id="tr-customer-detail-id">
				<td>Azonosító: <span id="id"><%-id%></span></td>
			</tr>
			<tr id="tr-customer-detail-customer-type">
				<td>Besorolás:</td> 
				<td colspan="3">
					<input type="radio" name="customer-detail-customer-type" value="FELAJANLO" checked>Felajánló&nbsp;
  					<input type="radio" name="customer-detail-customer-type" value="KERVENYEZO">Kérvényező
				</td>
			</tr>
			<tr>
				<td>Család/cég név: </td>
				<td><input type="text" maxlength="35" id="customer-detail-surname" value="<%-surname%>"/></td>
				<td>Kereszt név: </td>
				<td><input type="text" maxlength="35" id="customer-detail-forename" value="<%-forename%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Irsz/Város <input type="text" maxlength="4" id="customer-detail-zip" value="<%-zip%>" class="input-short"/></td>
				<td colspan="3"><input type="text" maxlength="35" id="customer-detail-city" value="<%-city%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Utca/házszám: </td>
				<td colspan="3"><input type="text" maxlength="50" id="customer-detail-street" value="<%-street%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Telefon +36 </td>
				<td colspan="3" ><input type="text" maxlength="12" id="customer-detail-phone" value="<%-phone%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Család gondozó</td>
				<td colspan="3"><input type="text" maxlength="50" id="customer-detail-additional-contact" value="<%-additional_contact%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Telefon +36</td>
				<td colspan="3"> <input type="text" maxlength="12" id="customer-detail-additional-contact-phone" value="<%-additional_contact_phone%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Státusz</td>
				<td colspan="3"><select id="customer-detail-customer-status" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Minősítés</td>
				<td colspan="3"><select id="customer-detail-qualification" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Adószám</td>
				<td colspan="3"> <input type="text" maxlength="20" id="customer-detail-tax-number" value="<%-tax_number%>" class="width100percent"/></td>
			</tr>
			<tr>
				<td>Tajszám</td>
				<td colspan="3"> <input type="text" maxlength="20" id="customer-detail-tb-number" value="<%-tb_number%>" class="width100percent"/></td>
			</tr>
			<tr>	
				<td>Megjegyzés</td>
				<td colspan="3"><textarea id="customer-detail-description" class="width100percent"><%-description%></textarea></td>
			</tr>
			<tr>
				<td>Szül.hely</td>
				<td> <input type="text" maxlength="35" id="customer-detail-birth-place" value="<%-birth_place%>" /></td>
				<td>Szül.idő</td>
				<td> 
					<input class="input-short" type="text" id="customer-detail-birth-date-year" value="<% print(Util.nvl(birth_date, '').substr(0,4)); %>" maxlength="4"/>/
					<input class="input-short" type="text" id="customer-detail-birth-date-month" value="<% print(Util.nvl(birth_date, '').substr(5,2)); %>" maxlength="2"/>/
					<input class="input-short" type="text" id="customer-detail-birth-date-day" value="<% print(Util.nvl(birth_date, '').substr(8,2)); %>" maxlength="2"/>
				<td>
			</tr>
			<tr id="tr-customer-detail-created">
				<td>Létrehozás</td>
				<td colspan="3"><%-created_info%></td>
			</tr>
			<tr id="tr-customer-detail-modified">
				<td>Módosítás</td>
				<td colspan="3"><%-modified_info%></td>
			</tr>
		</table>

		<div class="icon-save" title="Adatok mentése" id="customer-save"></div>
		<div class="icon-cancel" title="Változások elvetése" id="customer-cancel"></div>
		<br>
		<div id="customer-save-errors" style="display:none;">
			<table>
				<tr>
					<td><div class="icon-warning" title="Hibás ügyfél rögzítés!"></div></td>
					<td><div id="customer-detail-save-errors-div" class="errorText"></div></td>
				</tr>
			</table>
				
		</div>
		<br>
		<div id="customer-warning-request" style="display:none;">
		<div class="icon-important-mid-little"></div>
		Az ügyfélnek az utolsó igénye <span id="customer-warning-request-date"></span> napján volt! 
		</div>
		</script>
		<script type="text/x-custom-template" id="template-similar-customer-table">
			<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id%></td>
						<td><%-rows[row].full_name%></td>
						<td><%-rows[row].full_address%></td>
						<td><%-rows[row].phone%></td>
						<td><%-rows[row].qualification_local%></td>
						<td><%-rows[row].tax_number%></td>
						<td><%-rows[row].tb_number%></td>
						<td>
							<div class="icon-ok-little" onclick="openCustomerDetail('<%-rows[row].id%>');" title="Kiválasztja a már rögzített ügyfelet"></div>
						</td>
					</tr>
			<% } %>
		</script>
		
		<script type="text/x-custom-template" id="template-customer-history">
			<table>				
			<% for(var row in rows) { %>
				<tr>
					<td colspan="2"><h3><%-rows[row].data_type_local%></h3></td>	
				</tr>
				<tr>
					<td colspan="2"><%-rows[row].created_info%></td>
				</tr>
				<tr>
					<td>Régi érték: </td><td><%-rows[row].old_value%></td>
				</tr>
				<tr>
					<td>Új érték: </td><td><%-rows[row].new_value%></td>
				<tr>
				
			<% } %>
			</table>
		</script>
		
		<script type="text/javascript" src="js/lib/zips-1.0.js"></script>
		<script type="text/javascript" src="js/customer-0.3.js"></script>

		
    </body>
</html>