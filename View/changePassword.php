
	<?php
    	$_GET["site"] = 'passwordChange';
		require_once 'header.php';
	?>
	
	<?php
		echo '<input type="hidden" id="minPwdLength" value="' . Config::getContextParam("MIN_PASSWORD_LENGTH") . '"/>'; 
	?>
	
		<div class="mainArea" id="passwordChange">
	
			<table class="mainAreaTable">
				<tr>
					<td rowspan="4">
						<img src="images/key_refresh_2.png"/>
					</td>
					<td>Jelenlegi jelszó:</td>
					<td><input type="password" id="oldPassword"  maxlength="20" title="Jelenlegi jelszó megadása"/></td>
				</tr>
				<tr>
					<td>Új jelszó:</td>
					<td><input type="password" id="newPassword"  maxlength="20" title="Új jelszó"/></td>
				</tr>
				<tr>
					<td>Jelszó ismét:</td>
					<td><input type="password" id="newPasswordAgain"  maxlength="20" title="Új jelszó, megismétlése (nehogy elrontsuk :) )" /></td>
				</tr>
				<tr>
					<td colspan="2"><button id="sendChangePassword" title="Adatok elküldése a szervernek">Jelszó csere</button></td>
				</tr>
				<tr>
					<td colspan="2"><span id="result" class="errorText" title="Miért nem sikerült a művelet?"></span></td>
				</tr>
			</table>
			
		</div>	
		<div id="javascript">
			<script type="text/javascript" src="js/changePassword-0.1.js" defer="defer"></script>
		</div>
	 </body>
</html>