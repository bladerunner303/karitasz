
    <?php
    	$_GET["site"] = 'transport';
		require_once 'header.php';
	?>
		<input type="hidden" id="valid-phone-number-regexp" value="<?php echo Config::getContextParam("VALID_PHONE_NUMBER_REGEXP_JS"); ?>" />
		<input type="hidden" id="transport-selected-id" value="" />
		<div id="transport" class="mainArea">
			<img src="images/transport.png" class="titleIcon"/>
			<h2 class="title">Szállítások</h2>
			<br>
			<fieldset class="fieldSetSize">
			    <legend>Keresési feltételek:</legend>
			    	<table>
			    		<tr>
			    			<td>Dátum tól-ig: </td>
			    			<td>
			    				<input type="text" id="find-transport-begin-date"/>
			    				<input type="text" id="find-transport-end-date"/>
							<td>Ügyfél/státusz</td><td><input type="text" id="find-transport-text" class="width500 find"></input></td></tr>
			    	</table>
		    	
		  	</fieldset>
			<br>
			 <div style="float:left">Találatok maximális száma: 
		  		<input type="text" id="find-transport-result-max" value="<?php echo Config::getContextParam("DEFAULT_RESULT_SIZE"); ?>" class="input-short"/>
		    </div>&nbsp;
			<div class="icon-refresh" id="refresh-transport-list" title="Frissiti a táblázatot" ></div>
			<div class="icon-add" id="add-transport" title="Új tétel rögzítése"></div>
			<div class="icon-spreadsheet cursor-link" id="export-transport" title="Találatok exportja"></div>
		    
		
			<table id="transport-table" style="display: none">
				<thead>
					<tr>
						<th>Azon</th>
						<th>Dátum</th>
						<th>Státusz</th>
						<th>Létrehozás</th>
						<th>Módosítás</th>
						<th>Műveletek</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			
			</table>
			</div>
			
			<div id="transport-detail-general"></div>
			
			<div id="dialog-transport-address-add" title="Várakozó transzferek hozzáadása">
				<?php 
					$_GET["type"] = 'selector';
					require_once 'waitingTransports.php';
				?>
			
			</div>
			
			<div id="dialog-transport-address" title="Szállításhoz tartozó címek">
				<div id="dialog-transport-address-table">
				</div>
			</div>
			
			
			<div id="dialog-transport-address-item" title="Részteljesítés">
				<div id="dialog-transport-address-item-table">
				</div>
			</div>

			<div id="dialog-transport-address-wizzard" title="Varázsló">
			
				<div id="transport-address-wizzard-customer">
					<p>Ügyfél adatok</p>
					<table>
						<tr>
							<td>Azonosító (ha van)</td>
							<td><input type="text" id="transport-address-wizzard-customer-id"/>
						</tr>
						<tr>
							<td>Besorolás:</td> 
							<td >
								<input type="radio" name="transport-address-wizzard-customer-customer-type" value="FELAJANLO" >Felajánló&nbsp;
			  					<input type="radio" name="transport-address-wizzard-customer-customer-type" value="KERVENYEZO" checked>Kérvényező
							</td>
						</tr>
						<tr>
							<td>Család/cég név: </td>
							<td><input type="text" maxlength="35" id="transport-address-wizzard-customer-customer-surname" /></td>
						</tr>
						<tr>
							<td>Kereszt név: </td>
							<td><input type="text" maxlength="35" id="transport-address-wizzard-customer-customer-forename" class="width100percent"/></td>
						</tr>
						<tr>
							<td>Telefon +36 </td>
							<td ><input type="text" maxlength="20" id="transport-address-wizzard-customer-customer-phone" class="width100percent"/></td>
						</tr>
						<tr>
						<tr>
							<td>Email </td>
							<td ><input type="email" maxlength="105" id="transport-address-wizzard-customer-customer-email" class="width100percent"/></td>
						</tr>
					</table>
					
					<div class="icon-cancel" title="Változások elvetése" id="transport-address-wizzard-cancel"></div>
					<div class="icon-next" title="Következő" id="transport-address-wizzard-next"></div>
		
					<br>
					<div id="transport-address-wizzard-customer-errors" style="display:none;">
						<table>
							<tr>
								<td><div class="icon-warning" title="Hibás ügyfél adatok"></div></td>
								<td><div id="transport-address-wizzard-customer-errors-div" class="errorText"></div></td>
							</tr>
						</table>
							
					</div>
					
								
				
				</div>
				
				<div id="transport-address-wizzard-operation" style="display:none;">
					<p>Szállítás adatai</p>
					<table>
						<tr>
							<td>Irsz/Város 
								<input type="text" maxlength="4" id="transport-address-wizzard-operation-zip" class="input-short"/></td>
							<td>
								<input type="text" maxlength="35" id="transport-address-wizzard-operation-city" class="width100percent"/></td>
						</tr>
						<tr>
							<td>Utca/házszám: </td>
							<td ><input type="text" maxlength="50" id="transport-address-wizzard-operation-street" class="width100percent"/></td>
						</tr>
						
					</table>
					
					
					<table id="transport-address-wizzard-add-element-table">
						<tr>
							<td>Típus</td>
							<td>
								<select id="transport-address-wizzard-add-element-type" class="width500"></select>
								<div class="icon-add" title="Elem hozzáadása" id="transport-address-wizzard-new-element-type"></div>
							</td>
						</tr>
						<tr>
							<td>Mennyiség</td>
							<td><input id="transport-address-wizzard-add-element-type-number" type="number" min="0" max="10" step="1" value="1"/>
						</tr>
						<tr>
							<td>Leírás</td>
							<td><input type="text" maxlength="50" class="width500" id="transport-address-wizzard-add-element-name"></input></td>
						</tr>
						<tr>
							<td colspan="2">
								<div class="icon-add" title="Hozzáadás" id="transport-address-wizzard-add"></div>
								<div class="icon-clear" title="törlés" id="transport-address-wizzard-clear"></div>
							</td>
						</tr>
					</table>
					
					<div id="transport-address-wizzard-elements">
					</div>
					
					<div class="icon-cancel" title="Változások elvetése" id="transport-address-wizzard-cancel2"></div>
					<div class="icon-prev" title="Vissza" id="transport-address-wizzard-prev"></div>
					<div class="icon-save" title="Mentés" id="transport-address-wizzard-save"></div>
					
					<br>
					<div id="transport-address-wizzard-operation-errors" style="display:none;">
						<table>
							<tr>
								<td><div class="icon-warning" title="Hibás szállítás adatok"></div></td>
								<td><div id="transport-address-wizzard-operation-errors-div" class="errorText"></div></td>
							</tr>
						</table>
							
					</div>
					
				</div>
								
			</div>
			
			<script type="text/template" id="template-transport-address-wizzard-elements-table">
				<table class="pure-table">
					<thead>
						<tr>
							<td>Típus</td>
							<td>Mennyiség</td>
							<td>Leírás</td>
							<td>&nbsp;</td>
						</tr>
					</thead>	
					<tbody>
						<% for(var row in rows) { %>
						<tr>
							<td><%-rows[row].typeName%></td>
							<td><%-rows[row].number%></td>
							<td><%-rows[row].name%></td>
							<td>
								<div class="icon-trash-full cursor-link" onclick="removeTransportAddressWizzardElement('<%-rows[row].goods_type%>', '<%-rows[row].number%>', '<%-rows[row].name%>');" title="Törlés"></div>
							</td>
						</tr>
				 	<% } %>
					</tbody>
				</table>
			</script>
						
			<script type="text/template" id="template-transport-table">
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id_format%></td>
						<td><%-rows[row].transport_date%></td>
						<td><%-rows[row].status_local%></td>
						<td><%-rows[row].created_info%></td>
						<td><%-rows[row].modified_info%></td>
						<td>
							<div class="icon-edit cursor-link" onclick="openTransportDetail(<%-rows[row].id%>);" title="Részletek"></div>
							<div class="icon-address-book cursor-link" onclick="openAddress(<%-rows[row].id%>);" title="Címek"></div>
							<div class="icon-print cursor-link" title="Szállítási címek nyomtatása" onclick="printTransport(<%-rows[row].id%>);"></div>
						</td>
					</tr>
				 <% } %>
			</script>
		
		
			<script type="text/template" id="template-transport-detail-general"> 
			
			<table>
				<tr id="tr-transport-detail-id">
					<td>Azonosító: <span id="transport-detail-id"><%-id%></span></td>
				</tr>
				<tr>
					<td>Dátum:</td> 
					<td>
						<input type="text" id="transport-detail-transport-date" value="<%-transport_date%>"></input>
					</td>
				</tr>
				<tr>
					<td>Státusz:</td> 
					<td>
						<select id="transport-detail-status"></select>
					</td>
				</tr>
				<tr id="tr-transport-detail-created">
					<td>Létrehozás</td>
					<td colspan="3"><%-created_info%></td>
				</tr>
				<tr id="tr-transport-detail-modified">
					<td>Módosítás</td>
					<td colspan="3"><%-modified_info%></td>
				</tr>
				<tr>
					<td>Címek
					<div class="icon-add-little" onclick="addAddress(<%-id%>);" title="Új cím hozzáadás"></div>
					<% 	if (!Util.isNullOrEmpty(id)){
						 print ('<div class="icon-new-window" onclick="addAddressWizard();" title="Cím varázsló"></div>'); 
						} 	%>
					</td>
				</tr>
				<td colspan="2">
					<div id="transport-detail-address-table"></div>
				</td>	
				</tr>
				
			</table>

		<div class="icon-save" title="Adatok mentése" id="transport-detail-save" ></div>
		<div class="icon-cancel" title="Változások elvetése" id="transport-detail-cancel"></div>
		<div class="icon-print cursor-link" title="Szállítási címek nyomtatása" id="transport-detail-print"></div>

		<br>
		<div id="transport-save-errors" style="display:none;">
				
		</div>

		</script>
		
			<script type="text/template" id="template-transport-addresses-table">

				<table id="table-transport-addresses-table" class="pure-table">
				<thead >
					<td>Cím</td>
					<td>Ügyfél</td>
					<td>Kérvény</td>
					<td>Státusz</td>
						<% if (editable) { %>
							<td>Műveletek</td>
						<% } %>
				</thead>
				<tbody>

				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].address_format%></td>
						<td><%-rows[row].customer_format%></td>
						<td><%-rows[row].operation_id%></td>
						<td><%-rows[row].status_local%></td>
						<% if (editable) { %>
							<td style="min-width: 290px">
								<div class="icon-edit cursor-link" onclick="openTransportOperationItems('<%-rows[row].operation_id%>');" title="Részletek"></div>
								<div class="icon-trash-full-mid-little cursor-link" onclick="removeTransportAddress('<%-rows[row].operation_id%>');" title="Törlés"></div>
								<div class="icon-select cursor-link" onclick="setTransportAddressStatus('<%-rows[row].operation_id%>', 'BEFEJEZETT_TRANSPORT');" title="Sikeresre állít"></div>
								<div class="icon-cancel cursor-link" onclick="setTransportAddressStatus('<%-rows[row].operation_id%>', 'SIKERTELEN_TRANSPORT');" title="Sikertelenre állít"></div>
								<div class="icon-up cursor-link" onclick="moveAddress(<%-rows[row].order_indicator%>,'UP');" title="Előre"></div>
								<div class="icon-down cursor-link" onclick="moveAddress(<%-rows[row].order_indicator%>,'DOWN');" title="Hátra"></div>
								<div class="icon-text-editor cursor-link" onclick="showTransportAddressItems('<%-rows[row].id%>');" title="Részletes szállítandó elemek"></div>
								<div class="icon-user-available cursor-link" onclick="addNotes('<%-rows[row].operation_id%>');" title="Megjegyzés hozzáadása"></div>
							</td>
						<% } %>
					</tr>
				 <% } %>
			</script>
		
			<script type="text/template" id="template-transport-address-item-table">
			<table class="pure-table">
				<thead>
					<tr>
						<td>Típus</td>
						<td>Leírás</td>
						<td>státusz</td>
						<td>Műveletek</td>
					</tr>
				</thead>
				<tbody>	
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].goods_type_local%></td>		
						<td><%-rows[row].name%></td>
						<td><%-rows[row].status_local%></td>
						<td>
							<div class="icon-select cursor-link" onclick="setTransportAddressItemStatus('<%-rows[row].id%>', 'BEFEJEZETT_TRANSPORT');" title="Sikeresre állít"></div>
							<div class="icon-cancel cursor-link" onclick="setTransportAddressItemStatus('<%-rows[row].id%>', 'SIKERTELEN_TRANSPORT');" title="Sikertelenre állít"></div>
						</td>
					</tr>
				 <% } %>
		
				</tbody>
				</table>
			</script>
		
		<script type="text/javascript" src="js/lib/zips-1.0.js"></script>
		<script type="text/javascript" src="js/transportjs?v=0.7rc7"></script>
		
    </body>
</html>