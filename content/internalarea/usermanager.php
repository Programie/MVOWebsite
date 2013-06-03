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
			$usernames = array
			(
				str_replace(" ", "", $firstName . $lastName),
				str_replace(" ", "", $firstName . "_" . $lastName),
				str_replace(" ", "", $firstName . "." . $lastName),
				str_replace(" ", "", $firstName . "." . $lastName . substr($birthDate[2], 2, 2)),
				str_replace(" ", "", $firstName . $lastName . substr($birthDate[2], 2, 2)),
				str_replace(" ", "", $firstName . "_" . $lastName . substr($birthDate[2], 2, 2)),
				str_replace(" ", "", $firstName . "." . $lastName . substr($birthDate[2], 2, 2))
			);
			$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `username` = :username");
			foreach ($usernames as $tryUsername)
			{
				$query->execute(array
				(
					":username" => $tryUsername
				));
				if (!$query->rowCount())
				{
					$username = $tryUsername;
					break;
				}
			}
		}
		if ($username)
		{
			$queryData = array
			(
				":enabled" => (int) $_POST["usermanager_edituser_enabled"],
				":username" => $username,
				":email" => $_POST["usermanager_edituser_email"],
				":firstName" => $firstName,
				":lastName" => $lastName,
				":birthDate" => $birthDate[2] . "-" . $birthDate[1] . "-" . $birthDate[0]
			);
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
					echo "<div class='ok'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				}
				else
				{
					$userId = Constants::$pdo->lastInsertId();
					echo "<div class='ok'>Der Benutzer wurde erfolgreich erstellt.</div>";
				}
				if ($_POST["usermanager_edituser_sendcredentialsmail"])
				{
					$emailError = "";
					if ($_POST["usermanager_edituser_email"])
					{
						$password = substr(str_shuffle("abcdefghkmnpqrstuvwxyzABCDEFGHKMNPQRSTUVWXYZ23456789_-"), 0, 10);
						$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password, `forcePasswordChange` = '1' WHERE `id` = :id");
						$query->execute(array
						(
							":password" => Constants::$accountManager->encrypt($userId, $password),
							":id" => $userId
						));
						
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
						echo "<div class='error'>Die Email mit den Zugangsdaten konnte nicht versendet werden: " . $emailError . "</div>";
					}
				}
			}
			else
			{
				echo "<div class='error'>Beim Speichern ist ein Fehler aufgetreten!</div>";
			}
		}
		else
		{
			echo "<div class='error'>Es wurde kein freier Benutzername gefunden!</div>";
		}
	}
	else
	{
		echo "<div class='error'>Das eingegebene Geburtsdatum ist ung&uuml;ltig!</div>";
	}
}
?>

<div id="usermanager_tabs" class="no-print">
	<ul>
		<li><a href="#usermanager_tabs_users">Benutzer</a></li>
		<li><a href="#usermanager_tabs_groups">Gruppen</a></li>
		<li><a href="#usermanager_tabs_permissiongroups">Berechtigungsgruppen</a></li>
	</ul>
	<div id="usermanager_tabs_users">
		<button type="button" id="usermanager_users_addbutton">Benutzer erstellen</button>
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
							<td>" . htmlspecialchars($row->firstName) . "</td>
							<td>" . htmlspecialchars($row->lastName) . "</td>
							<td>" . htmlspecialchars($row->email) . "</td>
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
			$query= Constants::$pdo->query("SELECT `id`, `title` FROM `usergroups`");
			while ($row = $query->fetch())
			{
				echo "<li class='ui-state-default'>" . htmlspecialchars($row->title) . "</li>";
			}
			?>
		</ul>
	</div>
	<div id="usermanager_tabs_permissiongroups">
		<div class='info'>Diese Funktion steht derzeit noch nicht zur Verf&uuml;gung!</div>
	</div>
</div>

<div id="usermanager_edituser">
	<form id="usermanager_edituser_form" action="/internalarea/usermanager" method="post">
		<input type="hidden" id="usermanager_edituser_id" name="usermanager_edituser_id"/>
		<div id="usermanager_edituser_tabs">
			<ul>
				<li><a href="#usermanager_edituser_tabs_general">Allgemein</a></li>
				<li><a href="#usermanager_edituser_tabs_contact">Kontakt</a></li>
				<li><a href="#usermanager_edituser_tabs_options">Optionen</a></li>
				<li><a href="#usermanager_edituser_tabs_permissions">Berechtigungen</a></li>
			</ul>
			<div id="usermanager_edituser_tabs_general">
				<label for="usermanager_edituser_username">Benutzername:</label>
				<input type="text" id="usermanager_edituser_username" name="usermanager_edituser_username" class="input-user"/>
				
				<label for="usermanager_edituser_firstname">Vorname:</label>
				<input type="text" id="usermanager_edituser_firstname" name="usermanager_edituser_firstname"/>
				
				<label for="usermanager_edituser_lastname">Nachname:</label>
				<input type="text" id="usermanager_edituser_lastname" name="usermanager_edituser_lastname"/>
				
				<label for="usermanager_edituser_birthdate">Geburtsdatum:</label>
				<input type="text" id="usermanager_edituser_birthdate" name="usermanager_edituser_birthdate" class="date"/>
			</div>
			<div id="usermanager_edituser_tabs_contact">
				<label for="usermanager_edituser_email">Email-Adresse:</label>
				<input type="text" id="usermanager_edituser_email" name="usermanager_edituser_email" class="input-email"/>
			</div>
			<div id="usermanager_edituser_tabs_options">
				<div><input type="checkbox" id="usermanager_edituser_enabled" name="usermanager_edituser_enabled" value="1" checked="checked"/><label for="usermanager_edituser_enabled">Aktiviert</label></div>
				<div><input type="checkbox" id="usermanager_edituser_sendcredentialsmail" name="usermanager_edituser_sendcredentialsmail" value="1"/><label for="usermanager_edituser_sendcredentialsmail">Zugangsdaten versenden</label></div>
			</div>
			<div id="usermanager_edituser_tabs_permissions">
			</div>
		</div>
	</form>
</div>

<script type="text/javascript">
	$("#usermanager_users_addbutton").click(function()
	{
		$("#usermanager_edituser_form")[0].reset();
		$("#usermanager_edituser_id").val("");
		$("#usermanager_edituser").dialog("option", "title", "Benutzer erstellen");
		$("#usermanager_edituser").dialog("open");
	});
	$("#usermanager_users_table tbody tr").click(function()
	{
		var userId = $(this).attr("userid");
		$("#usermanager_edituser_form")[0].reset();
		$.ajax(
		{
			type : "GET",
			dataType : "json",
			url : "/internalarea/usermanager/getuserdata/" + userId,
			error : function(jqXhr, textStatus, errorThrown)
			{
				alert("Fehler beim Laden der Benutzerdaten!");
			},
			success : function(data, status, jqXhr)
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
		autoOpen : false,
		closeText : "Schlie&szlig;en",
		modal : true,
		resizable : false,
		width : 800,
		buttons :
		{
			"OK" : function()
			{
				if ($("#usermanager_edituser_firstname").val())
				{
					if ($("#usermanager_edituser_lastname").val())
					{
						if ($("#usermanager_edituser_birthdate").val())
						{
							if ($("#usermanager_edituser_birthdate").datepicker("getDate"))
							{
								var emailRegEx =/^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
								if (!$("#usermanager_edituser_email").val() || emailRegEx.test($("#usermanager_edituser_email").val()))
								{
									if (!$("#usermanager_edituser_sendcredentialsmail").prop("checked") || $("#usermanager_edituser_email").val())
									{
										if (!$("#usermanager_edituser_id").val() || !$("#usermanager_edituser_sendcredentialsmail").prop("checked") || ($("#usermanager_edituser_sendcredentialsmail").prop("checked") && confirm("Durch die Option 'Zugangsdaten versenden' wird ein neues Passwort generiert!\n\nWirklich fortfahren?")))
										{
											$("#usermanager_edituser_form")[0].submit();
										}
									}
									else
									{
										alert("Die Option 'Zugangsdaten versenden' erfordert die Angabe einer Email-Adresse!");
										$("#usermanager_edituser_tabs").tabs("option", "active", $("#usermanager_edituser_tabs_contact").parent().index());
									}
								}
								else
								{
									alert("Die eingegebene Email-Adresse hat ein ung\u00fcltiges Format!");
								}
							}
							else
							{
								alert("Das eingegebene Geburtsdatum ist ung\u00fcltig!");
							}
						}
						else
						{
							alert("Kein Geburtsdatum angegeben!");
						}
					}
					else
					{
						alert("Kein Nachname angegeben!");
					}
				}
				else
				{
					alert("Kein Vorname angegeben!");
				}
			},
			"Abbrechen" : function()
			{
				$(this).dialog("close");
			}
		}
	});
</script>