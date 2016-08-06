<!DOCTYPE html>

<?php 
require_once '../Util/Loader.php';

$error = '';
$userName = '';
$userPassword = '';

if ((isset($_POST["userName"])) && (isset($_POST["userPassword"]))) {
	if ((preg_match('/^[a-zA-Z]{1,20}$/',$_POST["userName"])) && (preg_match('/^[\s\S]{1,20}$/',$_POST["userPassword"]))) {
	
		$userName = $_POST["userName"];
		$userPassword = $_POST["userPassword"];
	}
	else {
		$error = 'Hibás felhasználó név vagy jelszó!';
	}
	
	if (strlen($userName .  $userPassword) != 0) {
				
		$result = User::login($userName, $userPassword);
		
		if ($result->isGood){
			
			$sessionId = Session::open($result->userId, $userName);
			setcookie("sessionId", $sessionId, time() + (10 * 365 * 24 * 60 * 60), "/");
			header('Location: customer.php');
						
		}
		else {
			//Write error;
			$error = $result->error;
		}
	}
}

?>


<html>
<head>
<meta charset="UTF-8"> 
<title>Karitász - Bejelentkezés</title>
<link rel="stylesheet" type="text/css" href="css/loginPage-0.1.css">
</head>
<body>
	<form action="login.php" method="post">
		<div class="designColor" id="header">
			<h1 class="fontColor" >Üdvözöljük a Karitász helper alkalmazásban!</h1>
		</div>
		<h1 >Bejelentkezés</h1>
		<div class="designColor fontColor" id="mainBox" >
			
			<div id="keyImage">
				<img src="images/key.png"/>
			</div>
			<div id="inputs">
				<span class="label">Felhasználó:</span>
				<input type="text" id="userName" name="userName" maxlength="20" value="<?php echo $userName ?>" />
				<br><br>
				<span class="label">Jelszó: </span>
				<input type="password" id="userPassword" maxlength="20" name="userPassword" />
				<br><br>
				<div class="centerPosition">
				<button id="send" >Bejelentkezés</button>
				<br>
				<span id="error" ><?php echo $error ?></span>
				</div>
			</div>
		</div>
	</form>
</body>

</html>



