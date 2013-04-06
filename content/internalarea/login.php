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
		echo "<div class='error'>Login fehlgeschlagen: Benutzername oder Passwort falsch!</div>";
	}
	echo "
		<form action='/internalarea/login' method='post'>
			<input type='text' class='input-user' id='loginform_username' name='username' placeholder='Benutzername' required/>
			<input type='password' name='password' placeholder='Passwort' required/>
			
			<div>
				<input type='submit' value='Login'/>
				<a href='/internalarea/resetpassword'>Passwort vergessen?</a>
			</div>
		</form>
	";
}
?>