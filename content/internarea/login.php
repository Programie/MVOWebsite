<h1>Anmelden</h1>

<?php
if (Constants::$accountManager->getUserId())
{
	echo "<p>Sie sind bereits angemeldet!</p>";
}
else
{
	echo "<p>Melden Sie sich im internen Bereich mit ihren Benutzerdaten an um auf weitere Bereiche zugreifen zu k&ouml;nnen.</p>";
	if (Constants::$accountManager->hasLoginFailed())
	{
		echo "<div class='error'>Login fehlgeschlagen: Benutzername oder Passwort falsch!</div>";
	}
	echo "
		<form action='/internarea/login' method='post'>
			<input type='text' id='loginform_username' name='username' placeholder='Benutzername' required/>
			<input type='password' name='password' placeholder='Passwort' required/>
			
			<div>
				<input type='submit' value='Login'/>
				<a href='/internarea/resetpassword'>Passwort vergessen?</a>
			</div>
		</form>
	";
}
?>