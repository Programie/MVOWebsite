<h1>Benutzerverwaltung</h1>

<?php
if (isset($_POST["usermanager_edituser_id"]))
{
	$birthDate = explode(".", $_POST["usermanager_edituser_birthdate"]);
	if ($_POST["usermanager_edituser_birthdate"] and checkdate($birthDate[1], $birthDate[0], $birthDate[2]))
	{
		$userId = $_POST["usermanager_edituser_id"];
		$username = $_POST["usermanager_edituser_username"];
		$firstName = $_POST["usermanager_edituser_firstname"];
		$lastName = $_POST["usermanager_edituser_lastname"];
		if (!$username)
		{
			$usernames = array(str_replace(" ", "", $firstName . $lastName), str_replace(" ", "", $firstName . "_" . $lastName), str_replace(" ", "", $firstName . "." . $lastName), str_replace(" ", "", $firstName . "." . $lastName . substr($birthDate[2], 2, 2)), str_replace(" ", "", $firstName . $lastName . substr($birthDate[2], 2, 2)), str_replace(" ", "", $firstName . "_" . $lastName . substr($birthDate[2], 2, 2)), str_replace(" ", "", $firstName . "." . $lastName . substr($birthDate[2], 2, 2)));
			$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `username` = :username");
			foreach ($usernames as $tryUsername)
			{
				$query->execute(array(":username" => $tryUsername));
				if (!$query->rowCount())
				{
					$username = $tryUsername;
					break;
				}
			}
		}
		if ($username)
		{
			$queryData = array(":enabled" => (int)$_POST["usermanager_edituser_enabled"], ":username" => $username, ":email" => $_POST["usermanager_edituser_email"], ":firstName" => $firstName, ":lastName" => $lastName, ":birthDate" => $birthDate[2] . "-" . $birthDate[1] . "-" . $birthDate[0]);
			if ($userId)
			{
				$queryData[":id"] = $userId;
				$query = Constants::$pdo->prepare("
					UPDATE `users`
					SET
						`enabled` = :enabled,
						`username` = :username,
						`email` = :email,
						`firstName` = :firstName,
						`lastName` = :lastName,
						`birthDate` = :birthDate
					WHERE `id` = :id
				");
			}
			else
			{
				$query = Constants::$pdo->prepare("
					INSERT INTO `users`
					(`enabled`, `username`, `email`, `firstName`, `lastName`, `birthDate`)
					VALUES(:enabled, :username, :email, :firstName, :lastName, :birthDate)
				");
			}
			if ($query->execute($queryData))
			{
				if ($userId)
				{
					$newUser = false;
				}
				else
				{
					$userId = Constants::$pdo->lastInsertId();
					$newUser = true;
				}

				$addQuery = Constants::$pdo->prepare("INSERT INTO `phonenumbers` (`userId`, `category`, `subCategory`, `number`) VALUES(:userId, :category, :subCategory, :number)");
				$updateQuery = Constants::$pdo->prepare("UPDATE `phonenumbers` SET `category` = :category, `subCategory` = :subCategory, `number` = :number WHERE `id` = :id AND `userId` = :userId");
				$entryIds = array();
				foreach ($_POST as $field => $value)
				{
					if (preg_match("/^usermanager_edituser_contact_([0-9]+)_number$/", $field, $matches))
					{
						if ($value)
						{
							$id = $matches[1];
							$updateQuery->execute(array(":id" => $id, ":userId" => $userId, ":category" => $_POST["usermanager_edituser_contact_" . $id . "_category"], ":subCategory" => $_POST["usermanager_edituser_contact_" . $id . "_subcategory"], ":number" => $value));
							$entryIds[] = intval($id);
						}
					}
					elseif (preg_match("/^usermanager_edituser_contact_new_([0-9]+)_number$/", $field, $matches))
					{
						if ($value)
						{
							$id = $matches[1];
							$addQuery->execute(array(":userId" => $userId, ":category" => $_POST["usermanager_edituser_contact_new_" . $id . "_category"], ":subCategory" => $_POST["usermanager_edituser_contact_new_" . $id . "_subcategory"], ":number" => $value));
							$entryIds[] = Constants::$pdo->lastInsertId();
						}
					}
				}
				if (!empty($entryIds))
				{
					$query = Constants::$pdo->prepare("DELETE FROM `phonenumbers` WHERE `userId` = :userId AND `id` NOT IN (" . implode(",", $entryIds) . ")");
					$query->execute(array(":userId" => $userId));
				}

				$permissionData = json_decode(file_get_contents(ROOT_PATH . "/includes/permissions.json"));
				function getObjectsByProperty($objectArray, $idProperty, $childrenProperty, $id, &$objects, $inArray = false)
				{
					foreach ($objectArray as $object)
					{
						if ($object->{$idProperty})
						{
							if ($inArray)
							{
								if (in_array($id, $object->{$idProperty}))
								{
									$objects[] = $object;
								}
							}
							else
							{
								if ($object->{$idProperty} == $id)
								{
									$objects[] = $object;
								}
							}
						}
						if ($object->{$childrenProperty} and !empty($object->{$childrenProperty}))
						{
							getObjectsByProperty($object->{$childrenProperty}, $idProperty, $childrenProperty, $id, $objects, $inArray);
						}
					}
				}

				function usermanager_removePermissionIds($groups, &$ids)
				{
					foreach ($groups as $group)
					{
						$item = array_search($group->id, $ids);
						if ($item !== false)
						{
							unset($ids[$item]);
						}
						if ($group->subGroups and !empty($group->subGroups))
						{
							usermanager_removePermissionIds($group->subGroups, $ids);
						}
					}
				}

				$groups = array();
				foreach ($_POST as $field => $value)
				{
					if (preg_match("/^usermanager_edituser_permissions_([0-9]+)$/", $field, $matches) and $value)
					{
						$groups[] = $matches[1];
					}
				}
				foreach ($groups as $group)
				{
					$nodes = array();
					getObjectsByProperty($permissionData, "id", "subGroups", $group, $nodes);
					if (!empty($nodes) and $nodes[0]->subGroups and !empty($nodes[0]->subGroups))
					{
						usermanager_removePermissionIds($nodes[0]->subGroups, $groups);
					}
				}
				$nodes = array();
				getObjectsByProperty($permissionData, "users", "subGroups", $userId, $nodes, true);
				foreach ($nodes as $node)
				{
					$node->users = array_values(array_diff($node->users, array($userId)));
				}
				foreach ($groups as $group)
				{
					$nodes = array();
					getObjectsByProperty($permissionData, "id", "subGroups", $group, $nodes);
					if (!empty($nodes) and $nodes[0])
					{
						$nodes[0]->users[] = (int)$userId;
						sort($nodes[0]->users, SORT_NUMERIC);
					}
				}
				file_put_contents(ROOT_PATH . "/includes/permissions.json", json_encode($permissionData));

				if ($newUser)
				{
					echo "<div class='alert-success'>Der Benutzer wurde erfolgreich erstellt.</div>";
				}
				else
				{
					echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				}

				if ($_FILES)
				{
					$profilePictureUploaded = false;
					$file = $_FILES["usermanager_edituser_profilepicture_file"];
					if ($file["name"])
					{
						if (!$file["error"])
						{
							if ($file["size"] > 1024 * 1024 * MAX_UPLOAD_SIZE)
							{
								echo "<div class='alert-error'>Die maximal erlaubte Dateigr&ouml;&szlig;e f&uuml;r das Profilbild ist " . MAX_UPLOAD_SIZE . " MB!</div>";
								$profilePictureUploadError = false;
							}
							else
							{
								$x = $_POST["usermanager_edituser_profilepicture_x"];
								$y = $_POST["usermanager_edituser_profilepicture_y"];
								$width = $_POST["usermanager_edituser_profilepicture_width"];
								$height = $_POST["usermanager_edituser_profilepicture_height"];
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
													$profilePictureUploaded = imagejpeg($resizedImage, ROOT_PATH . "/files/profilepictures/" . $userId . ".jpg");
												}
											}
										}
									}
								}
							}
						}
						if (!$profilePictureUploaded)
						{
							echo "<div class='alert-error'>Beim Hochladen des Profilbilds ist ein Fehler aufgetreten!</div>";
						}
					}
				}

				echo "<div class='alert-info'>Eventuell ge&auml;nderte Berechtigungen m&uuml;ssen &uuml;ber den Button <b>Berechtigungen &uuml;bernehmen</b> auf der Seite <b>Berechtigungsgruppen</b> &uuml;bernommen werden!</div>";

				if ($_POST["usermanager_edituser_sendcredentialsmail"])
				{
					$emailError = "";
					if ($_POST["usermanager_edituser_email"])
					{
						$password = substr(str_shuffle("abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ23456789_-"), 0, 10);
						$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password, `forcePasswordChange` = '1' WHERE `id` = :id");
						$query->execute(array(":password" => Constants::$accountManager->encrypt($userId, $password), ":id" => $userId));

						$mail = new Mail("Zugangsdaten für den internen Bereich");
						$mail->addReplacement("FIRSTNAME", $firstName);
						$mail->addReplacement("USERNAME", $username);
						$mail->addReplacement("PASSWORD", $password);
						$mail->setTemplate("credentialsmail");
						$mail->setTo(array($_POST["usermanager_edituser_email"] => $firstName . " " . $lastName));
						$mail->send();
					}
					else
					{
						$emailError = "F&uuml;r die Option 'Zugangsdaten versenden' muss eine g&uuml;tige Email-Adresse angegeben werden!";
					}
					if ($emailError)
					{
						echo "<div class='alert-error'>Die Email mit den Zugangsdaten konnte nicht versendet werden: " . $emailError . "</div>";
					}
				}
			}
			else
			{
				echo "<div class='alert-error'>Beim Speichern ist ein Fehler aufgetreten!</div>";
			}
		}
		else
		{
			echo "<div class='alert-error'>Es wurde kein freier Benutzername gefunden!</div>";
		}
	}
	else
	{
		echo "<div class='alert-error'>Das eingegebene Geburtsdatum ist ung&uuml;ltig!</div>";
	}
}
?>

<div id="usermanager_tabs">
	<ul>
		<li><a href="#usermanager_tabs_users">Benutzer</a></li>
		<li><a href="#usermanager_tabs_groups">Gruppen</a></li>
		<li><a href="#usermanager_tabs_permissiongroups">Berechtigungsgruppen</a></li>
	</ul>
	<div id="usermanager_tabs_users">
		<button type="button" id="usermanager_users_addbutton" class="no-print">Benutzer erstellen</button>
		<table id="usermanager_users_table" class="table tablesorter {sortlist: [[2,0],[1,0]]}">
			<thead>
			<tr>
				<th>Status</th>
				<th>Vorname</th>
				<th>Nachname</th>
				<th>Email</th>
				<th class="{sorter: 'number-attribute'}">Zuletzt Online</th>
			</tr>
			</thead>
			<tbody>
			<?php
			$query = Constants::$pdo->query("SELECT `id`, `enabled`, `email`, `firstName`, `lastName`, `lastOnline` FROM `users`");
			while ($row = $query->fetch())
			{
				$lastOnline = "";
				if ($row->lastOnline)
				{
					$lastOnline = explode(" ", $row->lastOnline);
					$lastOnlineDate = explode("-", $lastOnline[0]);
					$lastOnline = $lastOnlineDate[2] . "." . $lastOnlineDate[1] . "." . $lastOnlineDate[0] . " " . $lastOnline[1];
				}
				echo "
						<tr userid='" . $row->id . "'>
							<td><img src='/files/images/alerts/" . ($row->enabled ? "ok" : "error") . ".png' title='" . ($row->enabled ? "Aktiviert" : "Deaktiviert") . "'/></td>
							<td>" . escapeText($row->firstName) . "</td>
							<td>" . escapeText($row->lastName) . "</td>
							<td>" . escapeText($row->email) . "</td>
							<td number='" . strtotime($row->lastOnline) . "'>" . $lastOnline . "</td>
						</tr>
					";
			}
			?>
			</tbody>
		</table>
	</div>
	<div id="usermanager_tabs_groups">
		<ul id="usermanager_groups">
			<?php
			$query = Constants::$pdo->query("SELECT `id`, `title` FROM `usergroups`");
			while ($row = $query->fetch())
			{
				echo "<li class='ui-state-default'>" . htmlspecialchars($row->title) . "</li>";
			}
			?>
		</ul>
	</div>
	<div id="usermanager_tabs_permissiongroups">
		<button type="button" id="usermanager_permissiongroups_applybutton">Berechtigungen &uuml;bernehmen
		</button>
		<div id="usermanager_permissiongroups_tree"></div>
	</div>
</div>

<div id="usermanager_edituser">
	<form id="usermanager_edituser_form" action="/internalarea/usermanager" method="post" enctype="multipart/form-data">
		<input type="hidden" id="usermanager_edituser_id" name="usermanager_edituser_id"/>

		<div id="usermanager_edituser_tabs">
			<ul>
				<li><a href="#usermanager_edituser_tabs_general">Allgemein</a></li>
				<li><a href="#usermanager_edituser_tabs_profilepicture">Profilbild</a></li>
				<li><a href="#usermanager_edituser_tabs_contact">Kontakt</a></li>
				<li><a href="#usermanager_edituser_tabs_options">Optionen</a></li>
				<li><a href="#usermanager_edituser_tabs_permissions">Berechtigungen</a></li>
			</ul>
			<div id="usermanager_edituser_tabs_general">
				<label class="input-label" for="usermanager_edituser_username">Benutzername:</label>
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-user"></i></span>
					<input class="input-field" type="text" id="usermanager_edituser_username" name="usermanager_edituser_username"/>
				</div>

				<label class="input-label" for="usermanager_edituser_firstname">Vorname:</label>
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-user"></i></span>
					<input class="input-field" type="text" id="usermanager_edituser_firstname" name="usermanager_edituser_firstname"/>
				</div>

				<label class="input-label" for="usermanager_edituser_lastname">Nachname:</label>
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-user"></i></span>
					<input class="input-field" type="text" id="usermanager_edituser_lastname" name="usermanager_edituser_lastname"/>
				</div>

				<label class="input-label" for="usermanager_edituser_birthdate">Geburtsdatum:</label>
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-calendar"></i></span>
					<input class="input-field date" type="text" id="usermanager_edituser_birthdate" name="usermanager_edituser_birthdate"/>
				</div>
			</div>
			<div id="usermanager_edituser_tabs_profilepicture">
				<fieldset id="usermanager_edituser_profilepicture_current_fieldset">
					<legend>Aktuelles Profilbild</legend>
					<img id="usermanager_edituser_profilepicture_current_image" class="profilepicture"/>
				</fieldset>

				<fieldset>
					<legend>Neues Profilbild hochladen</legend>

					<p><b>Maximale Dateigr&ouml;&szlig;e:</b> <?php echo MAX_UPLOAD_SIZE; ?> MB</p>

					<input type="hidden" id="usermanager_edituser_profilepicture_x" name="usermanager_edituser_profilepicture_x"/>
					<input type="hidden" id="usermanager_edituser_profilepicture_y" name="usermanager_edituser_profilepicture_y"/>
					<input type="hidden" id="usermanager_edituser_profilepicture_width" name="usermanager_edituser_profilepicture_width"/>
					<input type="hidden" id="usermanager_edituser_profilepicture_height" name="usermanager_edituser_profilepicture_height"/>

					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo MAX_UPLOAD_SIZE * 1024 * 1024; ?>"/>
					<input type="file" id="usermanager_edituser_profilepicture_file" name="usermanager_edituser_profilepicture_file" onchange="usermanager_edituser_profilepicture_fileSelectHandler();"/>

					<div id="usermanager_edituser_profilepicture_editarea">
						<p>W&auml;hle den Bereich aus, welchen du als Profilbild verwenden m&ouml;chtest.</p>
						<img id="usermanager_edituser_profilepicture_preview"/>
					</div>
				</fieldset>
			</div>
			<div id="usermanager_edituser_tabs_contact">
				<label class="input-label" for="usermanager_edituser_email">Email-Adresse:</label>
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-envelope"></i></span>
					<input class="input-field" type="text" id="usermanager_edituser_email" name="usermanager_edituser_email"/>
				</div>

				<div id="usermanager_edituser_contact_div"></div>

				<button id="usermanager_edituser_contact_addbutton" type="button">Hinzuf&uuml;gen</button>
			</div>
			<div id="usermanager_edituser_tabs_options">
				<div><input type="checkbox" id="usermanager_edituser_enabled" name="usermanager_edituser_enabled" value="1" checked="checked"/><label for="usermanager_edituser_enabled">Aktiviert</label></div>
				<div><input type="checkbox" id="usermanager_edituser_sendcredentialsmail" name="usermanager_edituser_sendcredentialsmail" value="1"/><label for="usermanager_edituser_sendcredentialsmail">Zugangsdaten versenden</label></div>
			</div>
			<div id="usermanager_edituser_tabs_permissions">
				<div id="usermanager_edituser_permissions_tree"></div>
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
$("#usermanager_users_addbutton").click(function ()
{
	$("#usermanager_edituser_form")[0].reset();
	$("#usermanager_edituser_id").val("");
	$("#usermanager_edituser_profilepicture_current_fieldset").hide();
	$("#usermanager_edituser_profilepicture_current_image").attr("src", "");
	$("#usermanager_edituser_profilepicture_editarea").hide();
	$("#usermanager_edituser_contact_div").empty();
	$("#usermanager_edituser_permissions_tree").jstree("refresh");
	$("#usermanager_edituser").dialog("option", "title", "Benutzer erstellen");
	$("#usermanager_edituser").dialog("open");
});
$("#usermanager_users_table tbody tr").click(function ()
{
	var userId = $(this).attr("userid");
	$("#usermanager_edituser_form")[0].reset();
	$("#usermanager_edituser_contact_div").empty();
	$.ajax(
		{
			type: "GET",
			dataType: "json",
			url: "/internalarea/usermanager/getuserdata/" + userId,
			error: function (jqXhr, textStatus, errorThrown)
			{
				alert("Fehler beim Laden der Benutzerdaten!");
			},
			success: function (data, status, jqXhr)
			{
				if (data.id)
				{
					$("#usermanager_edituser_id").val(data.id);
					$("#usermanager_edituser_username").val(data.username);
					$("#usermanager_edituser_firstname").val(data.firstName);
					$("#usermanager_edituser_lastname").val(data.lastName);
					$("#usermanager_edituser_birthdate").datepicker("setDate", new Date(data.birthDate));
					$("#usermanager_edituser_email").val(data.email);
					$("#usermanager_edituser_enabled").prop("checked", data.enabled);
					$("#usermanager_edituser_profilepicture_editarea").hide();

					if (data.profilePictureUrl)
					{
						$("#usermanager_edituser_profilepicture_current_fieldset").show();
						$("#usermanager_edituser_profilepicture_current_image").attr("src", data.profilePictureUrl);
					}
					else
					{
						$("#usermanager_edituser_profilepicture_current_fieldset").hide();
						$("#usermanager_edituser_profilepicture_current_image").attr("src", "");
					}

					for (var index in data.phoneNumbers)
					{
						var phoneNumberData = data.phoneNumbers[index];
						usermanager_edituser_contact_addPhoneNumber(phoneNumberData.category, phoneNumberData.subCategory, phoneNumberData.number, phoneNumberData.id);
					}

					$("#usermanager_edituser_permissions_tree").jstree("refresh");

					$("#usermanager_edituser").dialog("option", "title", "Benutzer bearbeiten");
					$("#usermanager_edituser").dialog("open");
				}
				else
				{
					alert("Fehler beim Laden der Benutzerdaten!");
				}
			}
		});
});
$("#usermanager_tabs").tabs();
$("#usermanager_edituser_tabs").tabs();
$("#usermanager_groups").sortable();

$("#usermanager_edituser").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		modal: true,
		resizable: false,
		width: 800,
		buttons: [
			{
				id: "usermanager_edituser_ok",
				text: "OK",
				click: function ()
				{
					if ($("#usermanager_edituser_firstname").val())
					{
						if ($("#usermanager_edituser_lastname").val())
						{
							if ($("#usermanager_edituser_birthdate").val())
							{
								if ($("#usermanager_edituser_birthdate").datepicker("getDate"))
								{
									var emailRegEx = /^([\w\-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
									var emailField = $("#usermanager_edituser_email");
									if (!emailField.val() || emailRegEx.test(emailField.val()))
									{
										if (!$("#usermanager_edituser_sendcredentialsmail").prop("checked") || $("#usermanager_edituser_email").val())
										{
											if (!$("#usermanager_edituser_id").val() || !$("#usermanager_edituser_sendcredentialsmail").prop("checked") || ($("#usermanager_edituser_sendcredentialsmail").prop("checked") && confirm("Durch die Option 'Zugangsdaten versenden' wird ein neues Passwort generiert!\n\nWirklich fortfahren?")))
											{
												var okButton = $("#usermanager_edituser_ok");
												okButton.button("disable");
												$("#usermanager_edituser_cancel").button("disable");
												okButton.text("Wird \u00fcbernommen...");
												$("#usermanager_edituser_form")[0].submit();
											}
										}
										else
										{
											alert("Die Option 'Zugangsdaten versenden' erfordert die Angabe einer Email-Adresse!");
											$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_contact").index("#usermanager_edituser_tabs > div"));
										}
									}
									else
									{
										alert("Die eingegebene Email-Adresse hat ein ung\u00fcltiges Format!");
										$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_contact").index("#usermanager_edituser_tabs > div"));
									}
								}
								else
								{
									alert("Das eingegebene Geburtsdatum ist ung\u00fcltig!");
									$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_general").index("#usermanager_edituser_tabs > div"));
								}
							}
							else
							{
								alert("Kein Geburtsdatum angegeben!");
								$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_general").index("#usermanager_edituser_tabs > div"));
							}
						}
						else
						{
							alert("Kein Nachname angegeben!");
							$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_general").index("#usermanager_edituser_tabs > div"));
						}
					}
					else
					{
						alert("Kein Vorname angegeben!");
						$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_general").index("#usermanager_edituser_tabs > div"));
					}
				}
			},
			{
				id: "usermanager_edituser_cancel",
				text: "Abbrechen",
				click: function ()
				{
					$(this).dialog("close");
				}
			}
		]
	});

$("#usermanager_edituser_contact_addbutton").click(usermanager_edituser_contact_addPhoneNumber);

$("#usermanager_edituser_permissions_tree").jstree(
	{
		checkbox: {
			real_checkboxes: true,
			real_checkboxes_names: function (node)
			{
				return ["usermanager_edituser_permissions_" + $(node).data("groupId"), 1];
			}
		},
		core: {
			string: {
				loading: "Daten werden geladen...",
				new_node: "Neuer Eintrag"
			}
		},
		json_data: {
			ajax: {
				url: function ()
				{
					return "/internalarea/usermanager/getuserpermissions/" + $("#usermanager_edituser_id").val();
				}
			}
		},
		themes: {
			dots: true
		},
		types: {
			types: {
				permission: {
					icon: {
						image: "/files/images/usermanager/permission.png"
					}
				},
				permission_revoked: {
					icon: {
						image: "/files/images/usermanager/permission-revoked.png"
					}
				}
			}
		},
		plugins: ["checkbox", "json_data", "themes", "types", "ui"]
	});

function usermanager_edituser_profilepicture_fileSelectHandler()
{
	var file = $("#usermanager_edituser_profilepicture_file")[0].files[0];

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

	var previewImage = document.getElementById("usermanager_edituser_profilepicture_preview");

	var reader = new FileReader;
	reader.onload = function (event)
	{
		previewImage.src = event.target.result;
		previewImage.onload = function ()
		{
			$("#usermanager_edituser_profilepicture_editarea").slideDown(1000);

			if (typeof(usermanager_edituser_profilePicture_jcrop) != "undefined")
			{
				usermanager_edituser_profilePicture_jcrop.destroy();
			}

			$("#usermanager_edituser_profilepicture_preview").Jcrop(
				{
					aspectRatio: 1,
					boxWidth: 480,
					minSize: [200, 200],
					onSelect: function (coords)
					{
						$("#usermanager_edituser_profilepicture_x").val(coords.x);
						$("#usermanager_edituser_profilepicture_y").val(coords.y);
						$("#usermanager_edituser_profilepicture_width").val(coords.w);
						$("#usermanager_edituser_profilepicture_height").val(coords.h);
					}
				}, function ()
				{
					usermanager_edituser_profilePicture_jcrop = this;
				});
		}
	};
	reader.readAsDataURL(file);
}

$("#usermanager_permissiongroups_applybutton").click(function ()
{
	if (confirm("Sollen alle Berechtigungen jetzt \u00fcbernommen werden?"))
	{
		noty(
			{
				type: "success",
				text: "Die Berechtigungen werden \u00fcbernommen. Bitte warten..."
			});
		$("#usermanager_permissiongroups_applybutton").button("disable");
		$.ajax(
			{
				type: "GET",
				dataType: "json",
				url: "/internalarea/usermanager/applypermissions",
				error: function (jqXhr, textStatus, errorThrown)
				{
					$("#usermanager_permissiongroups_applybutton").button("enable");
					noty(
						{
							type: "error",
							text: "Fehler beim \u00dcbernehmen der Berechtigungen!"
						});
				},
				success: function (data, status, jqXhr)
				{
					$("#usermanager_permissiongroups_applybutton").button("enable");
					if (data.ok && !data.errors)
					{
						noty(
							{
								type: "success",
								text: "Es wurden " + data.ok + " Berechtigungen erfolgreich \u00fcbernommen."
							});
					}
					else
					{
						if (data.ok)
						{
							noty(
								{
									type: "warning",
									text: "Es wurden " + data.ok + " Berechtigungen erfolgreich \u00fcbernommen. " + data.errors + " Berechtigungen konnten nicht \u00fcbernommen werden!"
								});
						}
						else
						{
							noty(
								{
									type: "error",
									text: "Fehler beim \u00dcbernehmen der Berechtigungen!"
								});
						}
					}
				}
			});
	}
});

$("#usermanager_permissiongroups_tree").jstree(
	{
		core: {
			string: {
				loading: "Daten werden geladen...",
				new_node: "Neuer Eintrag"
			}
		},
		json_data: {
			ajax: {
				url: "/internalarea/usermanager/getpermissiongroups"
			}
		},
		themes: {
			dots: true
		},
		types: {
			types: {
				permission: {
					icon: {
						image: "/files/images/usermanager/permission.png"
					}
				},
				permission_revoked: {
					icon: {
						image: "/files/images/usermanager/permission-revoked.png"
					}
				},
				user: {
					icon: {
						image: "/files/images/usermanager/user.png"
					}
				}
			}
		},
		plugins: ["json_data", "themes", "types", "ui"]
	});

	var userManagerEditUserContactNewFieldId = 0;
	function usermanager_edituser_contact_addPhoneNumber(category, subCategory, number, id)
	{
		if (!id)
		{
			userManagerEditUserContactNewFieldId++;
			id = "new_" + userManagerEditUserContactNewFieldId;
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
		icon.addClass("el-icon-phone");
		iconSpan.append(icon);

		var fieldContainer = $("<div/>");
		fieldContainer.addClass("input-field");
		div.append(fieldContainer);

		var categorySelectBox = $("<select/>");
		categorySelectBox.addClass("input-select");
		categorySelectBox.attr("id", "usermanager_edituser_contact_" + id + "_category");
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
		subCategorySelectBox.attr("id", "usermanager_edituser_contact_" + id + "_subcategory");
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
		inputField.attr("id", "usermanager_edituser_contact_" + id + "_number");
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
		removeIcon.addClass("el-icon-trash");
		removeIconSpan.append(removeIcon);

		$("#usermanager_edituser_contact_div").append(div);
	}
</script>