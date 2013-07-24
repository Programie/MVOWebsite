<h1>Anmelden</h1>

<?php
if (Constants::$accountManager->getUserId())
{
	echo "<p>Du bist bereits angemeldet!</p>";
}
else
{
	echo "<p>Melde dich im internen Bereich mit deinem Benutzername und Passwort an um auf weitere Bereiche zugreifen zu k&ouml;nnen.</p>";
	if (Constants::$accountManager->hasLoginFailed())
	{
		echo "<p class='alert-error'>Login fehlgeschlagen: Benutzername oder Passwort falsch!</p>";
	}
	echo "
		<form action='" . $_SERVER["REQUEST_URI"] . "' method='post'>
			<div class='input-container'>
				<span class='input-addon'><i class='icon-user'></i></span>
				<input class='input-field' type='text' id='loginform_username' name='username' placeholder='Benutzername' required/>
			</div>
			<div class='input-container'>
				<span class='input-addon'><i class='icon-key'></i></span>
				<input class='input-field' type='password' name='password' placeholder='Passwort' required/>
			</div>
			
			<input type='submit' value='Login'/>
			<a href='/internalarea/resetpassword'>Passwort vergessen?</a>
		</form>
	";
}
?>