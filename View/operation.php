
    <?php
   		$type = empty($_GET['type'])? null : $_GET['type'];
    	$_GET["site"] = $type;
    	
		require_once 'header.php';
	?>
		<input type="hidden" id="operation-selected-id" value="" />
		<div id="operation" class="mainArea" >
		<?php 
			//request - kérvények, offer - felajánlások, üres - ha csak listázunk
			if ($type == 'request'){
				echo '<input type="hidden" id="operation-form-isEditable" value="true"/>';
				echo '<img src="images/request.png" class="titleIcon"/><h2 class="title">Kérvények</h2><input type="hidden" value="KERVENYEZES" id="operation-type"/>';
			}
			if ($type == 'offer'){
				echo '<input type="hidden" id="operation-form-isEditable" value="true"/>';
				echo '<img src="images/offer.png" class="titleIcon"/><h2 class="title">Felajánlások</h2><input type="hidden" value="FELAJANLAS" id="operation-type"/>';
			}
			if (empty($type)){
				echo '<input type="hidden" id="operation-form-isEditable" value="false"/>';
			}
		?>
			
			
			<br>
			<div <?php if (empty($type)){ echo 'style="display: none"';}?> >
			<fieldset class="fieldSetSize">
		    <legend>Keresési feltételek:</legend>
		    	<table>
		    		<tr>
		    			<td>Státusz: </td><td><select id="find-operation-status" class="width500"></select></td>
		    			<td>Ügyfél/<?php echo ($type == 'request')?'kérvény':'felajánlás'; ?>: </td><td><input type="text" id="find-operation-text" class="width500 find"></input></td>
		    		</tr>
		    		<tr><td colspan="4">Visszahívást vár <input type="checkbox" id="find-operation-callback"></input></td></tr>
		    	</table>
		    	
		  	</fieldset>
		  	<br>
		  	<div style="float:left">Találatok maximális száma: 
		  		<input type="text" id="find-operation-result-max" value="<?php echo Config::getContextParam("DEFAULT_RESULT_SIZE"); ?>" class="input-short"/>
		    </div>&nbsp;
			<div class="icon-refresh" id="refresh-operation-list" title="Frissiti a táblázatot" ></div>
			<div class="icon-add" id="add-operation" title="Új tétel rögzítése"></div>
			</div>
			<table id="operation-table" style="display: none">
				<thead>
					<tr>
						<td>Azonosító</td>
						<?php if (!empty($type)){ echo '<td>Ügyfél</td>'; } ?>
						<td>Státusz</td>
						<td>Létrehozás</td>
						<td>Módosítás</td>
						<td>Műveletek</td>
						<?php // if (!empty($type)){ echo '<td>Műveletek</td>'; } 	?>	
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
		
		<div id="operation-detail" class="mainArea" style="display: none">
			<div id="operation-detail-tabs">
			  <ul>
			    <li><a href="#operation-detail-general">Alapadatok</a></li>
			    <li><a href="#operation-detail-attachment" id="href-operation-detail-attachment">Mellékletek</a></li>
			    <li><a href="#operation-detail-transport">Szállítások</a></li>
			  </ul>
			    <div id="operation-detail-general"></div>
			    <div id="operation-detail-transport"></div>
			    <div id="operation-detail-attachment">
			    	<div id="operation-detail-attachement-div">
						<input name="operation-userfile" type="file" id="operation-userfile">
						<br>
						<input name="operation-upload" type="submit" id="operation-upload" value="Feltöltés">
					</div>
					<br>
					Már feltöltött mellékletek: 
			    	<br>
						<div id="operation-detail-attechments">
						</div>	
					<br>
					
			    </div>
			</div>
		
		
		</div>
		
		<script type="text/template" id="template-operation-table">
			<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id%></td>
						<?php if (!empty($type)){ echo '<td><%-rows[row].customer_format%></td>'; } ?>
						<td><%-rows[row].status_local%></td>
						<td><%-rows[row].created_info%></td>
						<td><%-rows[row].modified_info%></td>
						<td>
						<?php 
							if (!empty($type)){ 
								echo '<div class="icon-edit" onclick="openOpertaionDetail(<%-rows[row].id%>);" title="Részletek"></div>';
							}
						?>
							<% 
								if (rows[row].has_transport == 'Y') {
									print('<div class="icon-transport-mid" title="Szállításra vár! Cím: ' + rows[row].full_address_format + '"></div>' );
								}

								if (rows[row].is_wait_callback == 'Y') { 
									print('<div class="icon-call-start" title="Visszahívást vár! Tel: ' + rows[row].phone + '"></div>' ); 
							 	}
							%>
						</td>
					</tr>
			 <% } %>
		</script>
		<script type="text/template" id="template-operation-detail-general"> 
		<table>
			<tr id="tr-operation-detail-id">
				<td>Azonosító: <span id="operation-detail-id"><%-id%></span></td>
			</tr>
			<tr>
				<td>Státusz:</td> 
				<td>
					<select id="operation-detail-status" class="width100percent"></select>
				</td>
			</tr>
			<tr>
				<td>Ügyfél: </td>
				<td >
					<span id="operation-detail-customer-data"></span>
					<div class="icon-user-little" title="Ügyfél adatok keresése" id="operation-detail-customer-find"></div>
				</td>
			</tr>
			<tr id="tr-operation-detail-customer-address">
				<td>&nbsp;</td>
				<td >
					<span id="operation-detail-customer-address"></span>
					
				</td>
			</tr>
			<tr>
				<td>Visszahívást vár</td><td> <input type="checkbox" id="operation-detail-wait-callback"  <% if (is_wait_callback == 'Y') { print ('checked');} %>></input>
				</td>
			</tr>
			<tr>
				<td>Szállítás? </td><td><input type="checkbox" id="operation-detail-has-transport" <% if (has_transport == 'Y') { print ('checked');} %> ></input>
				</td>	
			</tr>
			<tr>	
				<td>Megjegyzés</td>
				<td ><textarea id="operation-detail-description" class="description"><%-description%></textarea></td>
			</tr>
			<tr <% if (operation_type=='FELAJANLAS') { print ('style="display: none"'); } %> >
				<td>Rászorultsági szint</td>
				<td><select id="operation-detail-neediness-level" class="width100percent"></select></td>
			</tr>
			<tr <% if (operation_type=='FELAJANLAS') { print ('style="display: none"'); } %> >
				<td>Jövedelem összeg és típus</td>
				<td><input type="text" id="operation-detail-income" class="width100percent" values="<%-income%>"/></td>
			</tr>
			<tr <% if (operation_type=='FELAJANLAS') { print ('style="display: none"'); } %> >
				<td>&nbsp;</td>
				<td><select id="operation-detail-income-type" class="width100percent"></select></td>
				
			</tr>
			<tr <% if (operation_type=='FELAJANLAS') { print ('style="display: none"'); } %> >
				<td>Háztartás egyéb jövedelme</td>
				<td><input type="text" id="operation-detail-others-income" class="width100percent" values="<%-others_income%>"/></td>
			</tr>
			<tr <% if (operation_type=='FELAJANLAS') { print ('style="display: none"'); } %> >
				<td>Beküldő (ha van)</td>
				<td><select id="operation-detail-sender" class="width100percent"></select></td>
			</tr>
			
			<tr id="tr-operation-detail-created">
				<td>Létrehozás</td>
				<td><%-created_info%></td>
			</tr>
			<tr id="tr-operation-detail-modified">
				<td>Módosítás</td>
				<td><%-modified_info%></td>
			</tr>
		</table>

		<br>
		<div id="operation-detail-elements">
		</div>
		<br>			

		<div class="icon-add" title="Új elem felvétele" id="operation-detail-new-element" style="display: none;"></div>
		<div class="icon-save" title="Adatok mentése" id="operation-detail-save" style="display: none;"></div>
		<div class="icon-cancel" title="Változások elvetése" id="operation-detail-cancel"></div>

		<br>
		<div id="operation-detail-save-errors" style="display:none;">
			<table>
				<tr>
					<td><div class="icon-warning" title="Hibás ügyfél rögzítés!"></div></td>
					<td><div id="operation-detail-save-errors-div" class="errorText"></div></td>
				</tr>
			</table>
				
		</div>

		</script>
		
		<script type="text/template" id="template-operation-detail-attachment-table">
			&nbsp;
			<table id="table-operation-attachment" class="pure-table">
				<thead >
					<td>Név</td>
					<td>Típus</td>
					<td>Létrehozás</td>
					<td>Méret (mb)</td>
					<td></td>
				</thead>
				<tbody>
					
					<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].extension%></td>
						<td><%-rows[row].created_info%></td>
						<td><%-rows[row].size_in_mb%></td>
						<td>
							<div class="icon-trash-full cursor-link" onclick="removeOperationDetailAttachment('<%-rows[row].id%>');" title="Törlés"></div>
							<div class="icon-download cursor-link" onclick="downloadOperationDetailAttachment('<%-rows[row].id%>');" title="Letöltés"></div>
						</td>
					</tr>
					 <% } %>
				</tbody>
			</table>
		
		</script>
		
		<script type="text/template" id="template-operation-detail-element-table">
			&nbsp;
			<table id="table-operation-element" class="pure-table">
				<thead >
					<td>No</td>
					<td>Név</td>
					<td>Típus</td>
					<td>Státusz</td>
					<td></td>
				</thead>
				<tbody>
					
					<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].order_indicator%></td>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].goods_type_local%></td>
						<td><%-rows[row].status_local%></td>
						<td>
							<!-- <div class="icon-edit-little" onclick="openPictures(<%-rows[row].id%>);" title="Képek"></div> -->
							<div class="icon-trash-full-mid-little" onclick="removeOperationDetailElement(<%-rows[row].order_indicator%>);" title="Törlés"></div>
							<div class="icon-select-mid-little" onclick="statusChangeOperationDetailElement(<%-rows[row].order_indicator%>);" title="Szállítás készre állítás"></div>
						</td>
					</tr>
					 <% } %>
				</tbody>
			</table>
		</script>
		
		
			<div id="dialog-add-element" title="Elem felvétele">
				<table>
					<tr>
						<td>Leírás</td>
						<td><input type="text" maxlength="50" class="width500" id="operation-detail-add-element-name"></input></td>
					</tr>
					<tr>
						<td>Típus</td>
						<td>
							<select id="operation-detail-add-element-type" class="width500"></select>
							<div class="icon-add" title="Elem hozzáadása" id="operation-detail-new-element-type"></div>
						</td>
					</tr>
					<tr id="tr-element-dialog-upload">
						<td><button id="operation-detail-add-element-upload">Kép feltöltés</button></td>
						<td></td>
					</tr>
					<tr>
						<div></div>
					</tr>
					<tr>
						<td></td>
						<td></td>
					</tr>
				</table>
				<div class="icon-save" title="Adatok mentése" id="operation-detail-add-element-save"></div>
				<div class="icon-cancel" title="Változások elvetése" id="operation-detail-add-element-cancel"></div>
				<br>
				<div id="element-save-errors" style="display:none;"></div>
			</div>
		
			<div id="dialog-customer">
				<?php 
					if (!empty($type)){
						$_GET["type"] = 'selector';
						require_once 'customer.php';
					 }
				?>
			</div>
		
		<script type="text/javascript" src="js/operation-0.3.js"></script>
		
    </body>
</html>