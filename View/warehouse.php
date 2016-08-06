
    <?php
    	$_GET["site"] = 'warehouse';
		require_once 'header.php';
	?>
		<div id="warehouse" class="mainArea">
			<img src="images/x-package-repository.png" class="titleIcon"/>
			<h2 class="title">Raktár</h2>
			<br>
			<fieldset class="fieldSetSize mainWidth">
		    <legend>Feltételek:</legend>
		    	Dátum: <input type="text" id="find-date"></input>
		    	&nbsp;
		    	Megnevezés: <input type="text" id="find-name"></input>
		  
		    </fieldset>
		     <br>
		    <div class="icon-refresh" id="refresh-warehouse-list" title="Frissiti a táblázatot" ></div>
			<br>
			<table id="warehouse-table" style="display: none">
				<thead>
					<tr>
						<th>Megnevezés</th>
						<th>Típus</th>
						<th>Beszállítás dátuma</th>
						<th>Beszállítás művelet</th>
						<th>Kiszállítás dátuma</th>
						<th>Kiszállítás művelet</th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
			
		</div>
		
		<script type="text/javascript" src="js/warehouse-0.1.js"></script>
		<script type="text/x-custom-template" id="template_warehouse_table">
				<% for(var row in rows) { %>
					<tr>
						<td><%-rows[row].name%></td>
						<td><%-rows[row].goodsType%></td>
						<td><%-rows[row].putInDate%></td>
						<td><span onclick="openOperation(<%-rows[row].putInOperationId%>);"><%-rows[row].putInOperationId%></span></td>
						<td><%-rows[row].putOutDate%></td>
						<td><span onclick="openOperation(<%-rows[row].putOutOperationId%>);"><%-rows[row].putOutOperationId%></span></td>
					</tr>
			 <% } %>
		</script>
		
    </body>
</html>