
    <?php
   		$type = empty($_GET['type'])? null : $_GET['type'];
    	$_GET["site"] = $type;
    	
		require_once 'header.php';
	?>
		<!-- Slideshow css and js -->
		<link rel="stylesheet" type="text/css" href="css/slideshow-0.6.css"/>
		<script type="text/javascript" src="js/lib/slideshow-0.6.js"></script>
		
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
			<div class="icon-spreadsheet cursor-link" id="export-operation" title="Találatok exportja"></div>
		    
			</div>
			<table id="operation-table" style="display: none">
				<thead>
					<tr>
						<th>Azonosító</th>
						<?php if (!empty($type)){ echo '<th>Ügyfél</th>'; } ?>
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
		
		<script type="text/template" id="template-operation-transport-table">
				<% for(var row in rows) { %>
				<div> <%-rows[row].transport_date%> &nbsp; <%-rows[row].id_format%> &nbsp; <%-rows[row].status_local%> </div>
				<table class="pure-table">
					<% for(var item in rows[row].items) { %>
						<tr>
							<td><%-rows[row].items[item].name_format%></td>
							<td><%-rows[row].items[item].status_local%></td>
						</tr>
					<% } %>
				</table>
				 <% } %>
		</script>
		
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
									print('<div class="icon-call-start" title="Visszahívást vár! Tel: ' + rows[row].phones + '"></div>' ); 
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
			<tr id="tr-operation-detail-last-status-changed">
				<td>Státusz váltás</td>
				<td><%-last_status_changed_info%></td>
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
					<td>Kapcsolt elem</td>
					<td>Műveletek</td>
				</thead>
				<tbody>
					
					<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].order_indicator%></td>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].goods_type_local%></td>
						<td><%-rows[row].status_local%></td>
						<td>
							<%-rows[row].related_operation_detail%>
							<div class="icon-cancel-mid-little" onclick="removeOperationDetailRelatedElement(<%-rows[row].order_indicator%>);" title="Kapcsolt elem törlése"
							<% if (Util.isNullOrEmpty(rows[row].related_operation_detail)) { 
								print ('style="display:none;"');
							}
							%>
							></div>
						</td>
						<td>
						<?php 
							if ($type == 'offer'){ 
								echo '<div class="icon-images" onclick="openPictures(<%-rows[row].order_indicator%>);" title="Képek"></div>';
							}
						?>
							<div class="icon-trash-full-mid-little" onclick="removeOperationDetailElement(<%-rows[row].order_indicator%>);" title="Törlés"></div>
							<div class="icon-select-mid-little" onclick="statusChangeOperationDetailElement(<%-rows[row].order_indicator%>);" title="Szállítás készre állítás"></div>
							<div class="icon-help-contents" onclick="showPotentialOperations('<%-rows[row].goods_type%>', <%-rows[row].order_indicator%>);" title="Lehetséges kérvény/felajánlás"></div>
						</td>
					</tr>
					 <% } %>
				</tbody>
			</table>
		</script>
		
		
		<script type="text/template" id="template-operation-detail-potential-operations">
			<br>
			&nbsp;
			Lehetséges felajánlások/kérvények: 
			<table id="table-operation-potential-operations" class="pure-table">
				<thead >
					<td>Azonosító</td>
					<td>Ügyfél</td>
					<td>Prioritás</td>
					<td>Megjegyzés</td>
					<td>Cím</td>
					<td>Létrehozás</td>
					<td></td>
				</thead>
				<tbody>
					
					<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id%></td>
						<td><%-rows[row].customer_format%></td>
						<td><%-rows[row].qualification_local%></td>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].full_address_format%></td>
						<td><%-rows[row].created_date%></td>
						<td>
							<div class="icon-select-mid-little" onclick="selectPotentialOperations('<%-rows[row].operation_detail_id%>', '<%-rows[row].customer_format%>', '<%-rows[row].id%>', '<%-rows[row].name%>');" title="Szállítás készre állítás"></div>
						</td>
					</tr>
					 <% } %>
				</tbody>
			</table>
		</script>
		
		<script type="text/template" id="template-operation-slideshow">
				<% for(var row in rows) { %>
					<div class="mySlides slideshow-fade">
				  	
			      		<div class="slideshow-numbertext"><%-rows[row].index+1%>/<%-rows.length%></div>
				    		<img src="<%-rows[row].src%>" style="max-width:600px">
				  		</div>
					</div>
					 <% } %>
					<br>

				  <a class="slideshow-prev" onclick="plusSlides(-1)">&#10094;</a>
				  <a class="slideshow-next" onclick="plusSlides(1)">&#10095;</a>
				
		</script>
		
		<div id="operation-dialogs" style="display: none">
		
			<div id="dialog-add-element" title="Elem felvétele">
				<table>
					<tr>
						<td>Típus</td>
						<td>
							<select id="operation-detail-add-element-type" class="width500"></select>
							<div class="icon-add" title="Elem hozzáadása" id="operation-detail-new-element-type"></div>
						</td>
					</tr>
					<tr>
						<td>Mennyiség</td>
						<td><input id="operation-detail-add-element-type-number" type="number" min="0" max="10" step="1" value="1"/>
					</tr>
					<tr>
						<td>Leírás</td>
						<td><input type="text" maxlength="50" class="width500" id="operation-detail-add-element-name"></input></td>
					</tr>
					<tr id="tr-element-dialog-upload">
						<td>
						<input name="operation-detail-add-element-upload-pics" type="submit" id="operation-detail-add-element-upload-pics" value="Feltöltés"/>
						<input id="operation-detail-add-element-upload" type="file" name="operation-detail-add-element-upload" accept="image/gif, image/jpeg"></input>
						</td>
						<td></td>
					</tr>
					<tr id="tr-element-pics-list" style="display:none;">
						<td colspan="2">
							<input type="hidden" id="operation-detail-add-element-element-pics-id-list"/>
							<div id="tr-element-pics-list-div"></div>
						</td>
					</tr>
					<tr id="tr-element-dialog-related-operation" style="display:none;">
						<td colspan="2">
							<div id="operation-detail-add-element-related-operation"></div>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<div id="operation-detail-add-element-potential-element"></div>
						</td>					
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
				<input type="hidden" id="operation-detail-add-element-related-detail"/>
				<input type="hidden" id="operation-detail-add-element-related-detail-format"/>
		
				<div id="dialog-customer">
					<?php 
						if (!empty($type)){
							$_GET["type"] = 'selector';
							require_once 'customer.php';
						 }
					?>
				</div>
	
				<div id="dialog-potentional-operations" title="Lehetséges kérvények/felajánlások">
					<div id="operation-potential-operations"></div>
				</div>
				
				<div id="dialog-operation-slideshow">
					<div class="slideshow-container" id="dialog-operation-slideshow-mySlides">
						<br>
					</div>
						<div style="text-align:center">
						  <span class="slideshow-dot" onclick="currentSlide(1)"></span>
						  <span class="slideshow-dot" onclick="currentSlide(2)"></span>
						  <span class="slideshow-dot" onclick="currentSlide(3)"></span>
						</div>
					
				</div>

			</div>
			
		</div>
			
		<script type="text/javascript" src="js/operation-0.7.rc2.js"></script>
	<?php 
	if (!empty($type)){
		echo '</body></html>';
	}
	?>
	