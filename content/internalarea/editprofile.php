<h1>Benutzerprofil bearbeiten</h1>

<?php
$userData = Constants::$accountManager->getUserData();
if ($userData->forcePasswordChange)
{
	echo "<p class='alert-warning' id='editprofile_passwordchangeinfo'>Du musst dein Passwort &auml;ndern bevor du auf die anderen Bereiche des internen Bereichs zugreifen kannst.</p>";
}
?>

<script type="text/javascript">
	var editProfileContactNewFieldId = 0;
	function editprofile_contact_addPhoneNumber(category, subCategory, number, id)
	{
		if (!id)
		{
			editProfileContactNewFieldId++;
			id = "new_" + editProfileContactNewFieldId;
		}
		var categories =
		{
			fax: "Fax",
			mobile: "Mobil",
			phone: "Telefon"
		};
		var subCategories =
		{
			business: "Gesch\u00e4ftlich",
			private: "Privat"
		};

		var div = $("<div/>");
		div.addClass("input-container");

		var iconSpan = $("<span/>");
		iconSpan.addClass("input-addon");
		div.append(iconSpan);

		var icon = $("<i/>");
		icon.addClass("icon-phone");
		iconSpan.append(icon);

		var fieldContainer = $("<div/>");
		fieldContainer.addClass("input-field");
		div.append(fieldContainer);

		var categorySelectBox = $("<select/>");
		categorySelectBox.addClass("input-select");
		categorySelectBox.attr("id", "editprofile_contact_" + id + "_category");
		categorySelectBox.attr("name", categorySelectBox.attr("id"));
		for (var index in categories)
		{
			var option = $("<option/>");
			option.attr("value", index);
			option.text(categories[index]);
			if (index == category)
			{
				option.prop("selected", true);
			}
			categorySelectBox.append(option);
		}
		fieldContainer.append(categorySelectBox);

		var subCategorySelectBox = $("<select/>");
		subCategorySelectBox.addClass("input-select");
		subCategorySelectBox.attr("id", "editprofile_contact_" + id + "_subcategory");
		subCategorySelectBox.attr("name", subCategorySelectBox.attr("id"));
		for (var index in subCategories)
		{
			var option = $("<option/>");
			option.attr("value", index);
			option.text(subCategories[index]);
			if (index == subCategory)
			{
				option.prop("selected", true);
			}
			subCategorySelectBox.append(option);
		}
		fieldContainer.append(subCategorySelectBox);

		var inputField = $("<input/>");
		inputField.attr("type", "text");
		inputField.attr("id", "editprofile_contact_" + id + "_number");
		inputField.attr("name", inputField.attr("id"));
		inputField.val(number);
		fieldContainer.append(inputField);

		var removeIconSpan = $("<span/>");
		removeIconSpan.addClass("input-addon");
		removeIconSpan.css("cursor", "pointer");
		removeIconSpan.attr("title", "Entfernen");
		removeIconSpan.click(function ()
		{
			div.remove();
		});
		fieldContainer.append(removeIconSpan);

		var removeIcon = $("<i/>");
		removeIcon.addClass("icon-trash");
		removeIconSpan.append(removeIcon);

		$("#editprofile_contact_div").append(div);
	}
</script>

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
						$query->execute(array(":firstName" => $_POST["editprofile_account_firstname"], ":lastName" => $_POST["editprofile_account_lastname"], ":id" => Constants::$accountManager->getUserId()));

						$userData = Constants::$accountManager->getUserData(); // Reload user data

						if ($usernameChanged)
						{
							$replacements = array("FIRSTNAME" => $userData->firstName, "OLDUSERNAME" => $oldUserData->username, "NEWUSERNAME" => $userData->username);
							$mail = new Mail("Benutzername geändert", $replacements);
							$mail->setTemplate("username-changed");
							$mail->setTo($userData->email);
							$mail->send();
						}

						echo "<p class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</p>";
					}
					else
					{
						echo "<p class='alert-error'>Der Benutzername wird bereits verwendet!</p>";
					}
				}
				else
				{
					echo "<p class='alert-error'>Der Nachname muss angegeben werden!</p>";
				}
			}
			else
			{
				echo "<p class='alert-error'>Der Vorname muss angegeben werden!</p>";
			}
		}
		else
		{
			echo "<p class='alert-error'>Ein Benutzername muss angegeben werden!</p>";
		}
	}
	?>

	<form action="/internalarea/editprofile#editprofile_account" method="post">
		<input type="hidden" name="editprofile_tab" value="account"/>

		<label class="input-label" for="editprofile_account_username">Benutzername:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-user"></i></span>
			<input class="input-field" type="text" id="editprofile_account_username" name="editprofile_account_username" value="<?php echo escapeText($userData->username); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_account_firstname">Vorname:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-user"></i></span>
			<input class="input-field" type="text" id="editprofile_account_firstname" name="editprofile_account_firstname" value="<?php echo escapeText($userData->firstName); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_account_lastname">Nachname:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-user"></i></span>
			<input class="input-field" type="text" id="editprofile_account_lastname" name="editprofile_account_lastname" value="<?php echo escapeText($userData->lastName); ?>" required/>
		</div>

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
					echo "<p class='alert-error'>Die maximal erlaubte Dateigr&ouml;&szlig;e ist 10 MB!</p>";
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
								if (imagecopyresampled($croppedImage, $sourceImage, 0, 0, $x, $y, $width, $height, $width, $height))
								{
									$resizedImage = resizeImage($croppedImage, 600, 600);
									if ($resizedImage)
									{
										if (imagejpeg($resizedImage, ROOT_PATH . "/files/profilepictures/" . $userData->id . ".jpg"))
										{
											echo "<p class='alert-success'>Dein Profilbild wurde erfolgreich aktualisiert.</p>";
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
			echo "<p class='alert-error'>Beim Hochladen ist ein Fehler ausgetreten. Bitte versuche es erneut oder wende dich an den Webmaster.</p>";
		}
	}

	$file = "/files/profilepictures/" . $userData->id . ".jpg";
	if (file_exists(ROOT_PATH . $file))
	{
		echo "
				<fieldset>
					<legend>Aktuelles Profilbild</legend>
					<img class='profilepicture' src='/getprofilepicture/" . $userData->id . "/" . md5_file(ROOT_PATH . $file) . "'/>
				</fieldset>
			";
	}
	?>

	<fieldset>
		<legend>Neues Profilbild hochladen</legend>

		<p><b>Maximale Dateigr&ouml;&szlig;e:</b> <?php echo MAX_UPLOAD_SIZE; ?> MB</p>

		<form id="editprofile_profilepicture_form" action="/internalarea/editprofile#editprofile_profilepicture" method="post" enctype="multipart/form-data">
			<input type="hidden" name="editprofile_tab" value="profilepicture"/>

			<input type="hidden" id="editprofile_profilepicture_x" name="editprofile_profilepicture_x"/>
			<input type="hidden" id="editprofile_profilepicture_y" name="editprofile_profilepicture_y"/>
			<input type="hidden" id="editprofile_profilepicture_width" name="editprofile_profilepicture_width"/>
			<input type="hidden" id="editprofile_profilepicture_height" name="editprofile_profilepicture_height"/>

			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_SIZE * 1024 * 1024; ?>"/>
			<input type="file" id="editprofile_profilepicture_file" name="editprofile_profilepicture_file" onchange="editprofile_profilePicture_FileSelectHandler();"/>

			<div id="editprofile_profilepicture_editarea">
				<p>W&auml;hle den Bereich aus, welchen du als Profilbild verwenden m&ouml;chtest.</p>
				<img id="editprofile_profilepicture_preview"/>
			</div>

			<button id="editprofile_profilepicture_upload" type="submit"><i class='icon-upload'></i> Hochladen</button>
		</form>

		<div id="editprofile_profilepicture_progressarea">
			<p>Das ausgew&auml;hlte Bild wird nun hochgeladen.</p>

			<div id="editprofile_profilepicture_progressbar">
				<div id="editprofile_profilepicture_progressbar_label"></div>
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
								<p class='alert-success'>Das Passwort wurde erfolgreich ge&auml;ndert.</p>
								<script type='text/javascript'>
									$('#editprofile_passwordchangeinfo').hide();
								</script>
							";
					}
					else
					{
						echo "<p class='alert-error'>Das eingegebene Passwort ist falsch!</p>";
					}
				}
				else
				{
					echo "<p class='alert-error'>Das neue Passwort stimmt nicht mit dem wiederholen Passwort &uuml;berein!</p>";
				}
			}
			else
			{
				echo "<p class='alert-error'>Das neue Passwort muss mindestens " . PASSWORDS_MINLENGTH . " Zeichen haben!</p>";
			}
		}
		else
		{
			echo "<p class='alert-error'>Das aktuelle Passwort muss angegeben werden!</p>";
		}
	}
	?>

	<form action="/internalarea/editprofile#editprofile_changepassword" method="post">
		<input type="hidden" name="editprofile_tab" value="password"/>

		<label class="input-label" for="editprofile_changepassword_current">Aktuelles Passwort:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-key"></i></span>
			<input class="input-field" type="password" id="editprofile_changepassword_current" name="editprofile_changepassword_current" value="<?php echo escapeText($_POST["editprofile_changepassword_current"]); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_changepassword_new1">Neues Passwort:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-key"></i></span>
			<input class="input-field" type="password" id="editprofile_changepassword_new1" name="editprofile_changepassword_new1" value="<?php echo escapeText($_POST["editprofile_changepassword_new1"]); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_changepassword_new2">Neues Passwort wiederholen:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-key"></i></span>
			<input class="input-field" type="password" id="editprofile_changepassword_new2" name="editprofile_changepassword_new2" value="<?php echo escapeText($_POST["editprofile_changepassword_new2"]); ?>" required/>
		</div>

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
							$query->execute(array(":email" => $_POST["editprofile_changeemail_new1"], ":id" => Constants::$accountManager->getUserId()));

							$userData = Constants::$accountManager->getUserData();

							$replacements = array("FIRSTNAME" => $userData->firstName, "NEWEMAILADDRESS" => $userData->newEmail, "URL" => BASE_URL . "/internalarea/confirmemail/" . strtotime($userData->newEmailChangeDate), "TIMEOUT" => date("d.m.Y H:i:s", strtotime($userData->newEmailChangeDate) + TIMEOUT_CONFIRMLINK));
							$mail = new Mail("Neue Email-Adresse bestätigen", $replacements);
							$mail->setTemplate("confirm-email");
							$mail->setTo($userData->newEmail);
							if ($mail->send())
							{
								echo "<p class='alert-info'>Es wurde eine Email mit dem Link zum Best&auml;tigen der Email-Adresse an die neue Email-Adresse gesendet.</p>";
							}
							else
							{
								echo "
									<p class='alert-error'>
										<p>Beim Senden der Email ist ein Fehler aufgetreten!</p>
										<p>Bitte versuche es sp&auml;ter erneut oder wende dich an den Webmaster.</p>
									</p
								";
							}
						}
						else
						{
							echo "<p class='alert-error'>Das eingegebene Passwort ist falsch!</p>";
						}
					}
					else
					{
						echo "<p class='alert-error'>Die neue Email-Adresse stimmt nicht mit der wiederholen Email-Adresse &uuml;berein!</p>";
					}
				}
				else
				{
					echo "<p class='alert-error'>Die neue Email-Adresse hat ein ung&uuml;tiges Format! Bitte verwende das Format <b>benutzername@domain.tld</b>.</p>";
				}
			}
			else
			{
				echo "<p class='alert-error'>Es muss eine neue Email-Adresse angegeben werden!</p>";
			}
		}
		else
		{
			echo "<p class='alert-error'>Das aktuelle Passwort muss angegeben werden!</p>";
		}
	}
	?>
	<form action="/internalarea/editprofile#editprofile_changeemail" method="post">
		<input type="hidden" name="editprofile_tab" value="email"/>

		<label class="input-label" for="editprofile_changeemail_currentpassword">Aktuelles Passwort:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-key"></i></span>
			<input class="input-field" type="password" id="editprofile_changeemail_currentpassword" name="editprofile_changeemail_currentpassword" value="<?php echo escapeText($_POST["editprofile_changeemail_currentpassword"]); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_changeemail_current">Aktuelle Email-Adresse:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-envelope"></i></span>
			<input class="input-field" type="text" id="editprofile_changeemail_current" value="<?php echo $userData->email; ?>" disabled/>
		</div>

		<label class="input-label" for="editprofile_changeemail_new1">Neue Email-Adresse:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-envelope"></i></span>
			<input class="input-field" type="text" id="editprofile_changeemail_new1" name="editprofile_changeemail_new1" value="<?php echo escapeText($_POST["editprofile_changeemail_new1"]); ?>" required/>
		</div>

		<label class="input-label" for="editprofile_changeemail_new2">Neue Email-Adresse wiederholen:</label>
		<div class="input-container">
			<span class="input-addon"><i class="icon-envelope"></i></span>
			<input class="input-field" type="text" id="editprofile_changeemail_new2" name="editprofile_changeemail_new2" value="<?php echo escapeText($_POST["editprofile_changeemail_new2"]); ?>" required/>
		</div>

		<input type="submit" value="Speichern"/>
	</form>
</div>

<div id="editprofile_contact">
	<?php
	if ($_POST["editprofile_tab"] == "contact")
	{
		$addQuery = Constants::$pdo->prepare("INSERT INTO `phonenumbers` (`userId`, `category`, `subCategory`, `number`) VALUES(:userId, :category, :subCategory, :number)");
		$updateQuery = Constants::$pdo->prepare("UPDATE `phonenumbers` SET `category` = :category, `subCategory` = :subCategory, `number` = :number WHERE `id` = :id AND `userId` = :userId");
		$entryIds = array();
		foreach ($_POST as $field => $value)
		{
			if (preg_match("/^editprofile_contact_([0-9]+)_number$/", $field, $matches))
			{
				if ($value)
				{
					$id = $matches[1];
					$updateQuery->execute(array(":id" => $id, ":userId" => Constants::$accountManager->getUserId(), ":category" => $_POST["editprofile_contact_" . $id . "_category"], ":subCategory" => $_POST["editprofile_contact_" . $id . "_subcategory"], ":number" => $value));
					$entryIds[] = intval($id);
				}
			}
			elseif (preg_match("/^editprofile_contact_new_([0-9]+)_number$/", $field, $matches))
			{
				if ($value)
				{
					$id = $matches[1];
					$addQuery->execute(array(":userId" => Constants::$accountManager->getUserId(), ":category" => $_POST["editprofile_contact_new_" . $id . "_category"], ":subCategory" => $_POST["editprofile_contact_new_" . $id . "_subcategory"], ":number" => $value));
					$entryIds[] = Constants::$pdo->lastInsertId();
				}
			}
		}
		if (!empty($entryIds))
		{
			$query = Constants::$pdo->prepare("DELETE FROM `phonenumbers` WHERE `userId` = :userId AND `id` NOT IN (" . implode(",", $entryIds) . ")");
			$query->execute(array(":userId" => Constants::$accountManager->getUserId()));
		}
		echo "<p class='alert-success'>Deine &Auml;nderungen wurden gespeichert.</p>";
	}
	?>
	<form action="/internalarea/editprofile#editprofile_contact" method="post">
		<input type="hidden" name="editprofile_tab" value="contact"/>

		<div id="editprofile_contact_div"></div>
		<script type="text/javascript">
			<?php
			$query = Constants::$pdo->prepare("SELECT `id`, `category`, `subCategory`, `number` FROM `phonenumbers` WHERE `userId` = :userId");
			$query->execute(array
			(
				":userId" => Constants::$accountManager->getUserId()
			));
			while ($row = $query->fetch())
			{
				echo "editprofile_contact_addPhoneNumber('" . $row->category . "', '" . $row->subCategory . "', '" . escapeText($row->number) . "', " . $row->id . ");";
			}
			?>
		</script>

		<button id="editprofile_contact_addbutton" type="button">
			<i class="icon-plus"></i>
			<span>Hinzuf&uuml;gen</span>
		</button>

		<input type="submit" value="Speichern"/>
	</form>
</div>
</div>

<script type="text/javascript">
	$("#editprofile_tabs").tabs();
	$("#editprofile_profilepicture_progressbar").progressbar(
	{
		change: function ()
		{
			$("#editprofile_profilepicture_progressbar_label").text($("#editprofile_profilepicture_progressbar").progressbar("value") + "%");
		}
	});

	$("#editprofile_profilepicture_form").ajaxForm(
	{
		beforeSubmit: function ()
		{
			$("#editprofile_profilepicture_progressbar").progressbar("value", 0);
			$("#editprofile_profilepicture_form").slideUp(1000, function ()
			{
				$("#editprofile_profilepicture_progressarea").slideDown(1000);
			});
		},
		uploadProgress: function (event, position, total, percentComplete)
		{
			$("#editprofile_profilepicture_progressbar").progressbar("value", percentComplete);
		},
		complete: function (response)
		{
			window.location.href = $("#editprofile_profilepicture_form").attr("action");
			window.location.reload();
		}
	});

	$("#editprofile_contact_addbutton").click(editprofile_contact_addPhoneNumber);

	function editprofile_profilePicture_FileSelectHandler()
	{
		var file = $("#editprofile_profilepicture_file")[0].files[0];

		if (file.type != "image/jpeg")
		{
			alert("Das ausgew\u00e4hlte Bild hat einen ung\u00fcltigen Dateintyp!\n\nBitte ein Bild vom Typ 'JPEG' (.jpg oder .jpeg) ausw\u00e4hlen.");
			return;
		}

		if (file.size > 1024 * 1024 * 10)// 10 MB
		{
			alert("Die maximal erlaubte Dateigr\u00f6\u00dfe ist 10 MB!");
			return;
		}

		var previewImage = document.getElementById("editprofile_profilepicture_preview");

		var reader = new FileReader;
		reader.onload = function (event)
		{
			previewImage.src = event.target.result;
			previewImage.onload = function ()
			{
				$("#editprofile_profilepicture_editarea").slideDown(1000);

				if (typeof(editprofile_profilePicture_jcrop) != "undefined")
				{
					editprofile_profilePicture_jcrop.destroy();
				}

				$("#editprofile_profilepicture_preview").Jcrop(
					{
						aspectRatio: 1,
						boxWidth: 480,
						minSize: [200, 200],
						onRelease: editprofile_profilePicture_onRelease,
						onSelect: editprofile_profilePicture_onSelect
					}, function ()
					{
						editprofile_profilePicture_jcrop = this;
					});
			}
		};
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