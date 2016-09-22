
    <?php
    	$_GET["site"] = 'transport';
		require_once 'header.php';
	?>
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
			  				</td></tr>
			    		<tr><td>Ügyfél: </td><td><input type="text" id="find-transport-customer" class="width500 find"></input></td></tr>
			    	</table>
		    	
		  	</fieldset>
			<br>
			 <div style="float:left">Találatok maximális száma: 
		  		<input type="text" id="find-transport-result-max" value="<?php echo Config::getContextParam("DEFAULT_RESULT_SIZE"); ?>" class="input-short"/>
		    </div>&nbsp;
			<div class="icon-refresh" id="refresh-transport-list" title="Frissiti a táblázatot" ></div>
			<div class="icon-add" id="add-transport" title="Új tétel rögzítése"></div>
		
			<table id="transport-table" style="display: none">
				<thead>
					<tr>
						<td>Azon</td>
						<td>Dátum</td>
						<td>Státusz</td>
						<td>Létrehozás</td>
						<td>Módosítás</td>
						<td>Műveletek</td>
					</tr>
				</thead>
				<tbody>
				</tbody>
			
			</table>
			</div>
			
			<div id="transport-detail-general"></div>
			<div id="dialog-transport-address" title="Szállításhoz tartozó címek">
				<table id="dialog-transport-address-table">
					<thead>
						<tr>
							<td>Felajánlás/Kérvény</td>
							<td>Cím</td>
							<td>Telefon</td>
							<td>Butorok</td>
							<td>Leírás</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1001</td>
							<td>1132 Budapest,Pitypang utca 34</td>
							<td>06-1-123456</td>
							<td>Kapucsengőn 8-as kód</td>
							<td>Komód, <br>
								Szekrény, <br>
								Hűtő
							</td>
						</tr>
						<tr>
							<td>1002</td>
							<td>1132 Budapest,Teszt tér 3</td>
							<td>06-1-123456</td>
							<td>Kapucsengőn 8-as kód</td>
							<td>Komód, <br>
								Szekrény, <br>
								Hűtő
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<div id="dialog-transport-address-add">
				<input type="text" id="find-transport-address-add" class="width500 find"></input>
				<table  id="dialog-transport-address-add-table">
					<thead>
						<tr>
							<td>Cím</td>
							<td>Telefon</td>
							<td>Butorok</td>
							<td>Kiválaszt</td>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>1132 Pitypang útca 34</td>
							<td>06-1-123456</td>
							<td>Szekrény<br>Komód</td>
							<td>
								<div class="icon-ok-little"></div>
							</td>
						</tr>
						<tr>
							<td>1132 Budapest,Teszt tér 3</td>
							<td>06-1-123456</td>
							<td>Hűtő</td>
							<td>
								<div class="icon-ok-little"></div>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			
			<script type="text/template" id="template-transport-table">
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id_format%></td>
						<td><%-rows[row].transport_date%></td>
						<td><%-rows[row].status_local%></td>
						<td><%-rows[row].created_info%></td>
						<td><%-rows[row].modified_info%></td>
						<td>
							<div class="icon-edit-little" onclick="openTransportDetail(<%-rows[row].id%>);" title="Részletek"></div>
							<div class="icon-edit-little" onclick="openAddress(<%-rows[row].id%>);" title="Címek"></div>
						</td>
					</tr>
				 <% } %>
			</script>
		
		
			<script type="text/template" id="template-transport-detail-general"> 
			
			<table>
				<tr>
					<td>Azonosító: <span id="id"><%-id%></span></td>
				</tr>
				<tr>
					<td>Státusz:</td> 
					<td>
						<select id="transport-status"></select>
					</td>
				</tr>
				<tr>
					<td>Dátum:</td> 
					<td>
						<input type="text" id="transport-date"></input>
					</td>
				</tr>
				<tr>
					<td>Címek
					<div class="icon-add-little" onclick="addAddress(<%-id%>);" title="Új cím hozzáadás"></div>
					</td>
				</tr>
				<td colspan="2">
					<div id="transport-detail-address-table"></div>
				</td>	
				</tr>
				<tr id="tr-created">
					<td>Létrehozás</td>
					<td colspan="3"><%-createdInfo%></td>
				</tr>
				<tr id="tr-modified">
					<td>Módosítás</td>
					<td colspan="3"><%-modifiedInfo%></td>
				</tr>
			</table>

		<div class="icon-save" title="Adatok mentése" id="transport-save" ></div>
		<div class="icon-cancel" title="Változások elvetése" id="transport-cancel"></div>

		<br>
		<div id="transport-save-errors" style="display:none;">
			<table>
				<tr>
					<td><div class="icon-warning" title="Hibás ügyfél rögzítés!"></div></td>
					<td><div id="role-save-errors-div" class="errorText"></div></td>
				</tr>
			</table>
				
		</div>

		</script>
		<script type="text/javascript" src="js/transport-0.4.js"></script>
		
    </body>
</html>