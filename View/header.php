<?php
	define('VERSION', '0.4-béta');
	define('SITE', 'karitasz');
	require_once '../Util/Loader.php';

	if (!SessionUtil::validSession()){
		header('Location: login.php');
		return '';
	}
	
	$site = $_GET["site"];
	if ($site == 'customer' ){
		$site = 'Karitász - Ügyfelek';
	} 
	else if ($site == 'warehouse'){
		$site = 'Karitász - Raktár';
	}
	else if ($site == 'request'){
		$site = 'Karitász - Kérvények';
	}
	else if ($site == 'offer'){
		$site = 'Karitász - Felajánlások';
	}
	else if ($site == 'transport'){
		$site = 'Karitász - Szállítások';
	}
	else if ($site == 'passwordChange'){
		$site = 'Karitász - Jelszócsere';
	}
	else {
		$site = 'Karitász';
	}
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" /> 
	<title><?php echo $site; ?></title>
	
	<!-- css -->
	<link rel="stylesheet" type="text/css" href="css/style-0.2.css">
	<link rel="stylesheet" type="text/css" href="css/gnome-icons-0.4.css">
	<link rel="stylesheet" type="text/css" href="css/jquery-ui.min.css"/> 
	<link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css"/>
	<link rel="stylesheet" type="text/css" href="css/pure-table-0.6.0.css"/>
	
	<!-- Javascript -->
	<script type="text/javascript" src="js/lib/jquery-2.2.4.min.js"></script>
	<script type="text/javascript" src="js/lib/jquery-ui-1.11.3.min.js"></script>
	<script type="text/javascript" src="js/lib/jquery.ui.datepicker-hu.js"></script>
	<script type="text/javascript" src="js/lib/jquery.dataTables-1.10.5.min.js"></script> 
	<script type="text/javascript" src="js/lib/jquery.numericField-1.0.js"></script> 
	<script type="text/javascript" src="js/lib/underscore-1.8.3.min.js"></script>
	<script type="text/javascript" src="js/header-0.4.js" defer="defer"></script>		
	
	<script type="text/javascript">
	
		$( document ).ready(function() {
			$( document ).tooltip();	
		});

		function checkSession(res){
			if (!res.validSession){
				window.location.replace('login.php');
			}
		}
	</script>
</head>
<body>
	<input type="hidden" id="server-time" value="<?php echo SystemUtil::getCurrentTimestamp(); ?>" />
	<input type="hidden" id="operation-max-repeat-month" value="<?php echo Config::getContextParam("OPERATON_MAX_REPEAT_MONTH"); ?>" />

	<div id="header">&nbsp;
		<h1><?php echo $site; ?></h1>
		
		<ul>
			<li><a href="customer.php">&nbsp;Ügyfelek&nbsp;</a></li>
			<!--   <li><a href="warehouse.php">&nbsp;Raktár&nbsp;</a></li> -->
			<li><a href="operation.php?type=request">&nbsp;Kérvények&nbsp;</a></li>
			<li><a href="operation.php?type=offer">&nbsp;Felajánlások&nbsp;</a></li>
			<li><a href="transport.php">&nbsp;Szállítások&nbsp;</a></li>
			<li><a href="changePassword.php" id="changePassword">&nbsp;Jelszó váltás&nbsp;</a>&nbsp;</li>
		</ul>
		
	</div>
	
	<div id="infoLine">
		<span class="littleInfo left">Verzió: <?php echo VERSION;?></span>
		<span id="logout" class="littleInfo right" >Kijelentkezés</span>
	</div>
	
	
