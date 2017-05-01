<?php
require_once '../Util/Loader.php';
//session ellenőrzés
if (!SessionUtil::validSession()){
	header('Location: login.php');
	return '';
}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	
	<!-- css -->
	<style type="text/css">
		.cursor-link{
			cursor: pointer;
		}
		
		.left{
		  display: block;
		  float:left;
		  width: 100px;
		}
		
		.right{
		  display: block;
		  float:right;
		  width: 100px;
		  margin-right: 80px;
		}
				
		
		body {
			font-size: 40px;
			background-color: lightgray;
		}
		
		table {
				width: 98%;
		}
		
		td {
			border: solid 1px;
			background-color: chartreuse;
			padding: 10px;
		}
		button{
			width: 250px;
			font-size: 30px;
		}
		
	</style>
	<!-- Javascript -->
	<script type="text/javascript" src="js/lib/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="js/lib/underscore-1.8.3.min.js"></script>
	<script type="text/javascript" src="js/header-0.6.js" defer="defer"></script>
	<script type="text/javascript" src="js/dailyTransport-0.7.rc4.js" defer="defer"></script>
			
	 
</head>
<body>
<input type="hidden" id="server-time" value="<?php echo SystemUtil::getCurrentTimestamp(); ?>" />
Mai napra a következő szállítások esnek
<br>
<a id="refresh-address-list" title="Frissit" class="cursor-link left">Frissit</a>
<a id="logout" class="cursor-link right">Kilépés</a>
<div id="tr-daily-transport"></div>


<script type="text/x-custom-template" id="template-transport-table">
	<table>
		<% for(var row in rows) { %>	
			<tr>
				<td >
					<span><b>Státusz:</b>&nbsp;<%-rows[row].status_local%></span><br> 
					<span><b>Cím:</b>&nbsp;<%-rows[row].address_format%></span><br>
					<span><b>Név:</b>&nbsp;<%-rows[row].customer_format%></span><br>
					<span><b>Telo:</b>&nbsp;<%-rows[row].customer_phone%></span><br>
					<ul>
						<% for (var n in rows[row].items) { %>
						<li><%-rows[row].items[n].name_format%></li>
					<% } %>			
					</ul>	
					<button id="add-description" onclick="addDescription('<%-rows[row].id%>');">Megjegyzés írás</button>&nbsp;
					<button id="set-successful" onclick="setSuccessful('<%-rows[row].id%>');">Sikeresre állít</button>&nbsp;
					<button id="set-canceled" onclick="setCanceled('<%-rows[row].id%>');">Sikertelenre állít</button>
					<div id="result"/>
				</td>
			</tr>
		<% } %>
				
	</table>
</script>

</body>
</html>