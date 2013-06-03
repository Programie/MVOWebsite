<h1>Benutzerprofil bearbeiten</h1>

<?php
$userData = Constants::$accountManager->getUserData();
if ($userData->forcePasswordChange)
{
	echo "<div class='warning' id='editprofile_passwordchangeinfo'>Du musst dein Passwort &auml;ndern bevor du auf die anderen Bereiche des internen Bereichs zugreifen kannst.</div>";
}
?>

<div id="editprofile_tabs">
	<ul>
		<li><a href="#editprofile_account">Account</a></li>
		<li><a href="#editprofile_profilepicture">Profilbild</a></li>
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
			<input type="text" class="input-user" id="editprofile_account_username" name="editprofile_account_username" value="<?php echo htmlspecialchars($userData->username);?>" required/>
			
			<label for="editprofile_account_firstname">Vorname:</label>
			<input type="text" id="editprofile_account_firstname" name="editprofile_account_firstname" value="<?php echo htmlspecialchars($userData->firstName);?>" required/>
			
			<label for="editprofile_account_lastname">Nachname:</label>
			<input type="text" id="editprofile_account_lastname" name="editprofile_account_lastname" value="<?php echo htmlspecialchars($userData->lastName);?>" required/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
	
	<div id="editprofile_profilepicture">
		<?php
		if ($_POST["editprofile_tab"] == "profilepicture")
		{
			$showError = true;
			if ($_FILES)
			{
				$file = $_FILES["editprofile_profilepicture_file"];
				if (!$file["error"])
				{
					if ($file["size"] > 1024 * 1024 * 10)
					{
						echo "<div class='error'>Die maximal erlaubte Dateigr&ouml;&szlig;e ist 10 MB!</div>";
						$showError = false;
					}
					else
					{
						$x = $_POST["editprofile_profilepicture_x"];
						$y = $_POST["editprofile_profilepicture_y"];
						$width = $_POST["editprofile_profilepicture_width"];
						$height = $_POST["editprofile_profilepicture_height"];
						if ($width > 0 and $height > 0)
						{
							$sourceImage = imagecreatefromjpeg($file["tmp_name"]);
							if ($sourceImage)
							{
								$croppedImage = imagecreatetruecolor($width, $height);
								if ($croppedImage)
								{
									if (imagecopyresampled($croppedImage, $sourceImage, 0, 0, $x,$y, $width, $height, $width, $height))
									{
										$resizedImage = resizeImage($croppedImage, 600, 600);
										if ($resizedImage)
										{
											if (imagejpeg($resizedImage, ROOT_PATH . "/files/profilepictures/" . $userData->id . ".jpg"))
											{
												echo "<div class='ok'>Dein Profilbild wurde erfolgreich aktualisiert.</div>";
												$showError = false;
											}
										}
									}
								}
							}
						}
					}
				}
			}
			if ($showError)
			{
				echo "<div class='error'>Beim Hochladen ist ein Fehler ausgetreten. Bitte versuche es erneut oder wende dich an den Webmaster.</div>";
			}
		}
		
		$file = "/files/profilepictures/" . $userData->id . ".jpg";
		if (file_exists(ROOT_PATH . $file))
		{
			echo "
				<fieldset>
					<legend>Aktuelles Profilbild</legend>
					<img class='profilepicture' src='" . $file . "?md5=" . md5_file(ROOT_PATH . $file) . "'/>
				</fieldset>
			";
		}
		?>
		
		<fieldset>
			<legend>Neues Profilbild hochladen</legend>
			<form id="editprofile_profilepicture_form" action="/internalarea/editprofile#editprofile_profilepicture" method="post" enctype="multipart/form-data">
				<input type="hidden" name="editprofile_tab" value="profilepicture"/>
				
				<input type="hidden" id="editprofile_profilepicture_x" name="editprofile_profilepicture_x"/>
				<input type="hidden" id="editprofile_profilepicture_y" name="editprofile_profilepicture_y"/>
				<input type="hidden" id="editprofile_profilepicture_width" name="editprofile_profilepicture_width"/>
				<input type="hidden" id="editprofile_profilepicture_height" name="editprofile_profilepicture_height"/>
				
				<input type="file" id="editprofile_profilepicture_file" name="editprofile_profilepicture_file" onchange="editprofile_profilePicture_FileSelectHandler();"/>
				
				<div id="editprofile_profilepicture_editarea">
					<p>W&auml;hle den Bereich aus, welchen du als Profilbild verwenden m&ouml;chtest.</p>
					<img id="editprofile_profilepicture_preview"/>
				</div>
				
				<input id="editprofile_profilepicture_upload" type="submit" value="Hochladen"/>
			</form>
			
			<div id="editprofile_profilepicture_progressarea">
				<p>Das ausgew&auml;hlte Bild wird nun hochgeladen.</p>
				<div id="editprofile_profilepicture_progressbar" class="progressbar">
					<span></span>
				</div>
			</div>
		</fieldset>
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
							echo "
								<div class='ok'>Das Passwort wurde erfolgreich ge&auml;ndert.</div>
								<script type='text/javascript'>
									$('#editprofile_passwordchangeinfo').hide();
								</script>
							";
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
			<input type="password" id="editprofile_changepassword_current" name="editprofile_changepassword_current" value="<?php echo htmlspecialchars($_POST["editprofile_changepassword_current"]);?>" required/>
			
			<label for="editprofile_changepassword_new1">Neues Passwort:</label>
			<input type="password" id="editprofile_changepassword_new1" name="editprofile_changepassword_new1" value="<?php echo htmlspecialchars($_POST["editprofile_changepassword_new1"]);?>" required/>
			
			<label for="editprofile_changepassword_new2">Neues Passwort wiederholen:</label>
			<input type="password" id="editprofile_changepassword_new2" name="editprofile_changepassword_new2" value="<?php echo htmlspecialchars($_POST["editprofile_changepassword_new2"]);?>" required/>
			
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
			<input type="password" id="editprofile_changeemail_currentpassword" name="editprofile_changeemail_currentpassword" value="<?php echo htmlspecialchars($_POST["editprofile_changeemail_currentpassword"]);?>" required/>
			
			<label for="editprofile_changeemail_current">Aktuelle Email-Adresse:</label>
			<input type="text" class="input-email" id="editprofile_changeemail_current" value="<?php echo $userData->email;?>" disabled/>
			
			<label for="editprofile_changeemail_new1">Neue Email-Adresse:</label>
			<input type="text" class="input-email" id="editprofile_changeemail_new1" name="editprofile_changeemail_new1" value="<?php echo htmlspecialchars($_POST["editprofile_changeemail_new1"]);?>" required/>
			
			<label for="editprofile_changeemail_new2">Neue Email-Adresse wiederholen:</label>
			<input type="text" class="input-email" id="editprofile_changeemail_new2" name="editprofile_changeemail_new2" value="<?php echo htmlspecialchars($_POST["editprofile_changeemail_new2"]);?>" required/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
	
	<div id="editprofile_contact">
		<?php
		if ($_POST["editprofile_tab"] == "contact")
		{
			$query = Constants::$pdo->prepare("UPDATE `users` SET `phonePrivate1` = :phonePrivate1, `phonePrivate2` = :phonePrivate2, `phoneWork` = :phoneWork, `phoneMobile` = :phoneMobile, `fax` = :fax WHERE `id` = :id");
			$query->execute(array
			(
				":phonePrivate1" => $_POST["editprofile_contact_phone_private1"],
				":phonePrivate2" => $_POST["editprofile_contact_phone_private2"],
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
			
			<label for="editprofile_contact_phone_private1">Telefon (Privat):</label>
			<input type="text" class="input-phone" id="editprofile_contact_phone_private1" name="editprofile_contact_phone_private1" value="<?php echo htmlspecialchars($userData->phonePrivate1);?>"/>
			
			<label for="editprofile_contact_phone_private2">Telefon (Privat):</label>
			<input type="text" class="input-phone" id="editprofile_contact_phone_private2" name="editprofile_contact_phone_private2" value="<?php echo htmlspecialchars($userData->phonePrivate2);?>"/>
			
			<label for="editprofile_contact_phone_work">Telefon (Gesch&auml;ftlich):</label>
			<input type="text" class="input-phone" id="editprofile_contact_phone_work" name="editprofile_contact_phone_work" value="<?php echo htmlspecialchars($userData->phoneWork);?>"/>
			
			<label for="editprofile_contact_phone_mobile">Mobil:</label>
			<input type="text" class="input-mobile-phone" id="editprofile_contact_phone_mobile" name="editprofile_contact_phone_mobile" value="<?php echo htmlspecialchars($userData->phoneMobile);?>"/>
			
			<label for="editprofile_contact_fax">Fax:</label>
			<input type="text" class="input-fax" id="editprofile_contact_fax" name="editprofile_contact_fax" value="<?php echo htmlspecialchars($userData->fax);?>"/>
			
			<input type="submit" value="Speichern"/>
		</form>
	</div>
</div>

<script type="text/javascript">
	$("#editprofile_tabs").tabs();
	
	$("#editprofile_profilepicture_form").ajaxForm(
	{
		beforeSubmit : function()
		{
			$("#editprofile_profilepicture_progressbar span").width("0%");
			$("#editprofile_profilepicture_form").slideUp(1000, function()
			{
				$("#editprofile_profilepicture_progressarea").slideDown(1000);
			});
		},
		uploadProgress : function(event, position, total, percentComplete)
		{
			$("#editprofile_profilepicture_progressbar span").width(percentComplete + "%");
		},
		complete : function(response)
		{
			window.location.href = $("#editprofile_profilepicture_form").attr("action");
			window.location.reload();
		}
	});
	
	function editprofile_profilePicture_FileSelectHandler()
	{
		var file = $("#editprofile_profilepicture_file")[0].files[0];
		
		if (file.type != "image/jpeg")
		{
			alert(unescape("Das ausgew%E4hlte Bild hat einen ung%FCltigen Dateintyp!\n\nBitte ein Bild vom Typ 'JPEG' (.jpg oder .jpeg) ausw%E4hlen."));
			return;
		}
		
		if (file.size > 1024 * 1024 * 10)// 10 MB
		{
			alert(unescape("Die maximal erlaubte Dateigr%F6%DFe ist 10 MB!"));
			return ;
		}
		
		var previewImage = document.getElementById("editprofile_profilepicture_preview");
		
		var reader = new FileReader;
		reader.onload = function(event)
		{
			previewImage.src = event.target.result;
			previewImage.onload = function()
			{
				$("#editprofile_profilepicture_editarea").slideDown(1000);
				
				if (typeof(editprofile_profilePicture_jcrop) != "undefined")
				{
					editprofile_profilePicture_jcrop.destroy();
				}
				
				$("#editprofile_profilepicture_preview").Jcrop(
				{
					aspectRatio : 1,
					boxWidth : 480,
					minSize : [200, 200],
					onRelease : editprofile_profilePicture_onRelease,
					onSelect : editprofile_profilePicture_onSelect
				}, function()
				{
					editprofile_profilePicture_jcrop = this;
				});
			}
		}
		reader.readAsDataURL(file);
	}
	
	function editprofile_profilePicture_onRelease()
	{
		$("#editprofile_profilepicture_upload").slideUp(1000);
	}
	
	function editprofile_profilePicture_onSelect(coords)
	{
		$("#editprofile_profilepicture_x").val(coords.x);
		$("#editprofile_profilepicture_y").val(coords.y);
		$("#editprofile_profilepicture_width").val(coords.w);
		$("#editprofile_profilepicture_height").val(coords.h);
		
		$("#editprofile_profilepicture_upload").slideDown(1000);
	}
</script>