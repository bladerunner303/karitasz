
    <?php
   		$type = empty($_GET['type'])? null : $_GET['type'];
    	$_GET["site"] = $type;
    	
		require_once 'header.php';
	?>
		<div id="waiting-transports" class="mainArea" >
			<br>
			<fieldset class="fieldSetSize">
		    <legend>Keresési feltételek:</legend>
		    	<input type="text" id="find-waiting-transport-operations-text" class="width500 find"></input>
		  	</fieldset>
		  	<br>
		  	<div class="icon-refresh" id="refresh-waiting-transport-operations-list" title="Frissiti a táblázatot" ></div>
			<div id="waiting-transport-operations-table"></div>
		</div>
				
		<script type="text/template" id="template-waiting-transport-operations-table">
			<table>
				<thead>
					<tr>
						<td>Azonosító</td>
						<td>Ügyfél</td>
						<td>Priorítás</td>
						<td>Várakozás</td>
						<td>Cím</td>
						<td>Műveletek</td>	
					</tr>
				</thead>
				<tbody>
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].id%></td>
						<td><%-rows[row].customer_format%></td>'
						<td><%-rows[row].priority_local%></td>
						<td><%-rows[row].last_status_changed%></td>
						<td><%-rows[row].address_format%></td>
						<td>
							<div class="icon-edit cursor-link" onclick="openTransportOperationItems(<%-rows[row].id%>);" title="Részletek"></div>
							<dic class="icon-ok cursor-link" onclick="selectTransportItem(<%-rows[row].id%>);" title="Szállítás  kiválasztása"></div>
						</td>
					</tr>
			 <% } %>
				</tbody>
			</table>
		
		</script>
		
		<script type="text/template" id="template-transport-operations-items-table">
		
			&nbsp;
			<table id="table-transport-operation-element" class="pure-table">
				<thead >
					<td>No</td>
					<td>Név</td>
					<td>Típus</td>
					<td>Státusz</td>
					<td>Kapcsolt elem</td>
				</thead>
				<tbody>
					
					<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].order_indicator%></td>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].goods_type_local%></td>
						<td><%-rows[row].status_local%></td>
						<td><%-rows[row].related_operation_detail%></td>
					</tr>
					 <% } %>
				</tbody>
			</table>

		</script>


		<div id="dialog-transports-items" title="Kapcsolodó elemek">
			<div id="waiting-transports-transport-items"></div>
		</div>
		<script type="text/javascript" src="js/waitingTransports-0.6.js"></script>
		
    </body>
</html>