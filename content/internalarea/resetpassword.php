<h1>Passwort zur&uuml;cksetzen</h1>

<?php
if (Constants::$accountManager->getUserId())
{
	echo "<p>Du bist derzeit angemeldet! Bitte verwende die Seite <a href='/internalarea/editprofile#editprofile_changepassword'>Benutzerprofil</a> um das Passwort zu &auml;ndern.</p>";
}
else
{
	$showResetForm = true;
	
	if (Constants::$pagePath[2])
	{
		$data = explode("-", Constants::$pagePath[2]);
		if (count($data) == 2)
		{
			$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `id` = :id AND `resetPasswordDate` IS NOT NULL AND `resetPasswordDate` = :date");
			$query->execute(array
			(
				":id" => $data[0],
				":date" => date("Y-m-d H:i:s", $data[1])
			));
			if ($query->rowCount())
			{
				$row = $query->fetch();
				if ($data[1] >= time() - TIMEOUT_CONFIRMLINK)
				{
					$showChangeForm = true;
					
					if (isset($_POST["resetpassword_password1"]) and isset($_POST["resetpassword_password2"]))
					{
						if (strlen($_POST["resetpassword_password1"]) >= PASSWORDS_MINLENGTH)
						{
							if ($_POST["resetpassword_password1"] == $_POST["resetpassword_password2"])
							{
								Constants::$accountManager->loginWithUserId($row->id);
								Constants::$accountManager->changePassword($_POST["resetpassword_password1"]);
								
								$showChangeForm = false;
								
								echo "<div class='ok'>Das Passwort wurde erfolgreich ge&auml;ndert. Sie sind nun angemeldet.</div>";
							}
							else
							{
								echo "<div class='error'>Die eigegebenen Passw&ouml;rter stimmen nicht &uuml;berein!</div>";
							}
						}
						else
						{
							echo "<div class='error'>Bitte verwenden Sie ein Passwort mit mindestens " . PASSWORDS_MINLENGTH . " Zeichen!</div>";
						}
					}
					
					if ($showChangeForm)
					{
						echo "
							<p>Geben Sie ein neues Passwort ein.</p>
							
							<form action='/internalarea/resetpassword/" . Constants::$pagePath[2] . "' method='post'>
								<input type='password' name='resetpassword_password1' placeholder='Neues Passwort' required/>
								<input type='password' name='resetpassword_password2' placeholder='Passwort wiederholen' required/>
								
								<input type='submit' value='OK'/>
							</form>
						";
					}
					
					$showResetForm = false;
				}
				else
				{
					echo "<div class='error'>Die G&uuml;ltigkeit des Schl&uuml;ssels ist abgelaufen! Bitte versuchen Sie es erneut oder wenden Sie sich an den Webmaster.</div>";
				}
			}
			else
			{
				echo "<div class='error'>Ung&uuml;ltiger Schl&uuml;ssel!</div>";
			}
		}
		else
		{
			echo "<div class='error'>Ung&uuml;ltiger Schl&uuml;ssel!</div>";
		}
	}
	
	if ($showResetForm)
	{
		echo "<p>Wenn Sie ihr Passwort vergessen haben, k&ouml;nnen Sie dieses auf dieser Seite zur&uuml;cksetzen.</p>";
		
		if (isset($_POST["resetpassword_username"]))
		{
			$time = time();
			
			$query = Constants::$pdo->prepare("SELECT `id`, `email`, `firstName`, `lastName` FROM `users` WHERE `username` = :username");
			$query->execute(array
			(
				":username" => $_POST["resetpassword_username"]
			));
			if ($query->rowCount())
			{
				$row = $query->fetch();
				
				$query = Constants::$pdo->prepare("UPDATE `users` SET `resetPasswordDate` = :date WHERE `id` = :id");
				$query->execute(array
				(
					":date" => date("Y-m-d H:i:s", $time),
					":id" => $row->id
				));
				
				$key = $row->id . "-" . $time;
				
				$replacements = array
				(
					"FIRSTNAME" => $row->firstName,
					"LASTNAME" => $row->lastName,
					"URL" => BASE_URL . "/internalarea/resetpassword/" . $key,
					"KEY" => $key,
					"TIMEOUT" => date("d.m.Y H:i:s", $time + TIMEOUT_CONFIRMLINK)
				);
				
				$mail = new Mail("Passwort zurücksetzen", $replacements);
				$mail->setTemplate("resetpassword");
				$mail->setTo($row->email);
				if ($mail->send())
				{
					echo "<div class='info'>Es wurde eine Email mit dem Link zum Zur&uuml;cksetzen des Passworts an die im Benutzeraccount hinterlegte Email-Adresse gesendet.</div>";
				}
				else
				{
					echo "
						<div class='error'>
							<p>Beim Senden der Email ist ein Fehler aufgetreten!</p>
							<p>Bitte versuchen Sie es sp&auml;ter erneut oder wenden Sie sich an den Webmaster.</p>
						</div>
					";
				}
			}
			else
			{
				echo "<div class='error'>Der Benutzername existiert nicht!</div>";
			}
		}
		echo "
			<form action='/internalarea/resetpassword' method='post'>
				<input type='text' id='resetpassword_username' name='resetpassword_username' placeholder='Benutzername' required/>
				
				<input type='submit' value='Senden'/>
			</form>
		";
	}
}
?>