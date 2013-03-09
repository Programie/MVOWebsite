<h1>Benutzerprofil bearbeiten</h1>

<?php
$userData = Constants::$accountManager->getUserData();
?>

<div id="editprofile_tabs">
	<ul>
		<li><a href="#editprofile_account">Account</a></li>
		<li><a href="#editprofile_changepassword">Passwort &auml;ndern</a></li>
		<li><a href="#editprofile_changeemail">Email-Adresse &auml;ndern</a></li>
		<li><a href="#editprofile_contact">Kontakt</a></li>
	</ul>
	
	<div id="editprofile_account">
		<?php
		if ($_POST["editprofile_tab"] == "account")
		{
			if ($_POST["editprofile_account_username"])
			{
				if ($_POST["editprofile_account_firstname"])
				{
					if ($_POST["editprofile_account_lastname"])
					{
						$oldUserData = clone $userData;
						$usernameChanged = false;
						if ($userData->username != $_POST["editprofile_account_username"])
						{
							$ok = Constants::$accountManager->changeUsername($_POST["editprofile_account_username"]);
							$usernameChanged = true;
						}
						else
						{
							$ok = true;
						}
						if ($ok)
						{
							$query = Constants::$pdo->prepare("UPDATE `users` SET `firstName` = :firstName, `lastName` = :lastName WHERE `id` = :id");
							$query->execute(array
							(
								":firstName" => $_POST["editprofile_account_firstname"],
								":lastName" => $_POST["editprofile_account_lastname"],
								":id" => Constants::$accountManager->getUserId()
							));
							
							$userData = Constants::$accountManager->getUserData();// Reload user data
							
							if ($usernameChanged)
							{
								$replacements = array
								(
									"FIRSTNAME" => $userData->firstName,
									"OLDUSERNAME" => $oldUserData->username,
									"NEWUSERNAME" => $userData->username
								);
								$mail = new Mail("Benutzername geändert", $replacements);
								$mail->setTemplate("username-changed");
								$mail->setTo($userData->email);
								$mail->send();
							}
							
							echo "<div class='ok'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
						}
						else
						{
							echo "<div class='error'>Der Benutzername wird bereits verwendet!</div>";
						}
					}
					else
					{
						echo "<div class='error'>Der Nachname muss angegeben werden!</div>";
					}
				}
				else
				{
					echo "<div class='error'>Der Vorname muss angegeben werden!</div>";
				}
			}
			else
			{
				echo "<div class='error'>Ein Benutzername muss angegeben werden!</div>";
			}
		}
		?>
		
		<form action="/internalarea/editprofile#editprofile_account" method="post">
			<input type="hidden" name="editprofile_tab" value="account"/>
			
			<label for="editprofile_account_username">Benutzername:</label>
			<input type="text" id="editprofile_account_username" name="editprofile_account_username" value="<?php echo $userData->username;?>" required/>
			
			<label for="editprofile_account_firstname">Vorname:</label>
			<input type="text" id="editprofile_account_firstname" name="editprofile_account_firstname" value="<?php echo $userData->firstName;?>" required/>
			
			<label for="editprofile_account_lastname">Nachname:</label>
			<input type="text" id="editprofile_account_lastname" name="editprofile_account_lastname" value="<?php echo $userData->lastName;?>" required/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
	
	<div id="editprofile_changepassword">
		<?php
		if ($_POST["editprofile_tab"] == "password")
		{
			if ($_POST["editprofile_changepassword_current"])
			{
				if ($_POST["editprofile_changepassword_new1"] and strlen($_POST["editprofile_changepassword_new1"]) >= PASSWORDS_MINLENGTH)
				{
					if ($_POST["editprofile_changepassword_new1"] == $_POST["editprofile_changepassword_new2"])
					{
						if (Constants::$accountManager->changePassword($_POST["editprofile_changepassword_new1"], $_POST["editprofile_changepassword_current"]))
						{
							echo "<div class='ok'>Das Passwort wurde erfolgreich ge&auml;ndert.</div>";
						}
						else
						{
							echo "<div class='error'>Das eingegebene Passwort ist falsch!</div>";
						}
					}
					else
					{
						echo "<div class='error'>Das neue Passwort stimmt nicht mit dem wiederholen Passwort &uuml;berein!</div>";
					}
				}
				else
				{
					echo "<div class='error'>Das neue Passwort muss mindestens " . PASSWORDS_MINLENGTH . " Zeichen haben!</div>";
				}
			}
			else
			{
				echo "<div class='error'>Das aktuelle Passwort muss angegeben werden!</div>";
			}
		}
		?>
		
		<form action="/internalarea/editprofile#editprofile_changepassword" method="post">
			<input type="hidden" name="editprofile_tab" value="password"/>
			
			<label for="editprofile_changepassword_current">Aktuelles Passwort:</label>
			<input type="password" id="editprofile_changepassword_current" name="editprofile_changepassword_current" value="<?php echo $_POST["editprofile_changepassword_current"];?>" required/>
			
			<label for="editprofile_changepassword_new1">Neues Passwort:</label>
			<input type="password" id="editprofile_changepassword_new1" name="editprofile_changepassword_new1" value="<?php echo $_POST["editprofile_changepassword_new1"];?>" required/>
			
			<label for="editprofile_changepassword_new2">Neues Passwort wiederholen:</label>
			<input type="password" id="editprofile_changepassword_new2" name="editprofile_changepassword_new2" value="<?php echo $_POST["editprofile_changepassword_new2"];?>" required/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
	
	<div id="editprofile_changeemail">
		<?php
		if ($_POST["editprofile_tab"] == "email")
		{
			if ($_POST["editprofile_changeemail_currentpassword"])
			{
				if ($_POST["editprofile_changeemail_new1"])
				{
					if (preg_match("/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,4}$/i", $_POST["editprofile_changeemail_new1"]))
					{
						if ($_POST["editprofile_changeemail_new1"] == $_POST["editprofile_changeemail_new2"])
						{
							if (Constants::$accountManager->checkPassword($_POST["editprofile_changeemail_currentpassword"]))
							{
								$query = Constants::$pdo->prepare("UPDATE `users` SET `newEmail` = :email, `newEmailChangeDate` = NOW() WHERE `id` = :id");
								$query->execute(array
								(
									":email" => $_POST["editprofile_changeemail_new1"],
									":id" => Constants::$accountManager->getUserId()
								));
								
								$userData = Constants::$accountManager->getUserData();
								
								$replacements = array
								(
									"FIRSTNAME" => $userData->firstName,
									"NEWEMAILADDRESS" => $userData->newEmail,
									"URL" => BASE_URL . "/internalarea/confirmemail/" . strtotime($userData->newEmailChangeDate),
									"TIMEOUT" => date("d.m.Y H:i:s", strtotime($userData->newEmailChangeDate) + TIMEOUT_CONFIRMLINK)
								);
								$mail = new Mail("Neue Email-Adresse bestätigen", $replacements);
								$mail->setTemplate("confirm-email");
								$mail->setTo($userData->newEmail);
								if ($mail->send())
								{
									echo "<div class='info'>Es wurde eine Email mit dem Link zum Best&auml;tigen der Email-Adresse an die neue Email-Adresse gesendet.</div>";
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
								echo "<div class='error'>Das eingegebene Passwort ist falsch!</div>";
							}
						}
						else
						{
							echo "<div class='error'>Die neue Email-Adresse stimmt nicht mit der wiederholen Email-Adresse &uuml;berein!</div>";
						}
					}
					else
					{
						echo "<div class='error'>Die neue Email-Adresse hat ein ung&uuml;tiges Format! Bitte verwende das Format <b>benutzername@domain.tld</b>.</div>";
					}
				}
				else
				{
					echo "<div class='error'>Es muss eine neue Email-Adresse angegeben werden!</div>";
				}
			}
			else
			{
				echo "<div class='error'>Das aktuelle Passwort muss angegeben werden!</div>";
			}
		}
		?>
		<form action="/internalarea/editprofile#editprofile_changeemail" method="post">
			<input type="hidden" name="editprofile_tab" value="email"/>
			
			<label for="editprofile_changeemail_currentpassword">Aktuelles Passwort:</label>
			<input type="password" id="editprofile_changeemail_currentpassword" name="editprofile_changeemail_currentpassword" value="<?php echo $_POST["editprofile_changeemail_currentpassword"];?>" required/>
			
			<label for="editprofile_changeemail_current">Aktuelle Email-Adresse:</label>
			<input type="text" id="editprofile_changeemail_current" value="<?php echo $userData->email;?>" disabled/>
			
			<label for="editprofile_changeemail_new1">Neue Email-Adresse:</label>
			<input type="text" id="editprofile_changeemail_new1" name="editprofile_changeemail_new1" value="<?php echo $_POST["editprofile_changeemail_new1"];?>" required/>
			
			<label for="editprofile_changeemail_new2">Neue Email-Adresse wiederholen:</label>
			<input type="text" id="editprofile_changeemail_new2" name="editprofile_changeemail_new2" value="<?php echo $_POST["editprofile_changeemail_new2"];?>" required/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
	
	<div id="editprofile_contact">
		<?php
		if ($_POST["editprofile_tab"] == "contact")
		{
			$query = Constants::$pdo->prepare("UPDATE `users` SET `phonePrivate` = :phonePrivate, `phoneWork` = :phoneWork, `phoneMobile` = :phoneMobile, `fax` = :fax WHERE `id` = :id");
			$query->execute(array
			(
				":phonePrivate" => $_POST["editprofile_contact_phone_private"],
				":phoneWork" => $_POST["editprofile_contact_phone_work"],
				":phoneMobile" => $_POST["editprofile_contact_phone_mobile"],
				":fax" => $_POST["editprofile_contact_fax"],
				":id" => Constants::$accountManager->getUserId()
			));
			
			$userData = Constants::$accountManager->getUserData();
			
			echo "<div class='ok'>Deine &Auml;nderungen wurden gespeichert.</div>";
		}
		?>
		<form action="/internalarea/editprofile#editprofile_contact" method="post">
			<input type="hidden" name="editprofile_tab" value="contact"/>
			
			<label for="editprofile_contact_phone_private">Telefon (Privat):</label>
			<input type="text" id="editprofile_contact_phone_private" name="editprofile_contact_phone_private" value="<?php echo $userData->phonePrivate;?>"/>
			
			<label for="editprofile_contact_phone_work">Telefon (Gesch&auml;ftlich):</label>
			<input type="text" id="editprofile_contact_phone_work" name="editprofile_contact_phone_work" value="<?php echo $userData->phoneWork;?>"/>
			
			<label for="editprofile_contact_phone_mobile">Mobil:</label>
			<input type="text" id="editprofile_contact_phone_mobile" name="editprofile_contact_phone_mobile" value="<?php echo $userData->phoneMobile;?>"/>
			
			<label for="editprofile_contact_fax">Fax:</label>
			<input type="text" id="editprofile_contact_fax" name="editprofile_contact_fax" value="<?php echo $userData->fax;?>"/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
</div>

<script type="text/javascript">
	$("#editprofile_tabs").tabs();
</script>