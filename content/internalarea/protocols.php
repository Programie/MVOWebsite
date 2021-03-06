<?php
$userGroups = array();
$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups` WHERE `id`");
while ($row = $query->fetch())
{
	$userGroups[$row->name] = $row->title;
}
?>
<h1>Protokolle</h1>

<?php
if (Constants::$accountManager->hasPermission("protocols.upload"))
{
	$userData = Constants::$accountManager->getUserData();
	if ($_POST["protocols_upload_confirmed"])
	{
		$error = "Beim Hochladen ist ein Fehler aufgetreten! Bitte versuche es erneut oder wende dich an den Webmaster.";

		$date = explode(".", $_POST["protocols_upload_date"]);
		if (checkdate($date[1], $date[0], $date[2]))
		{
			$permissionQuery = Constants::$pdo->prepare("SELECT `userId` FROM `permissions` WHERE `permission` = :permission");
			$userQuery = Constants::$pdo->prepare("SELECT `email`, `firstName`, `lastName` FROM `users` WHERE `id` = :id AND `enabled`");

			$mailRecipients = array();
			$groups = array();
			foreach ($_POST as $field => $value)
			{
				if (substr($field, 0, 24) == "protocols_upload_groups_" and $value)
				{
					$group = substr($field, 24);

					$groups[] = $group;

					$permissionQuery->execute(array(":permission" => "groups." . $group));
					while ($permissionRow = $permissionQuery->fetch())
					{
						$userQuery->execute(array(":id" => $permissionRow->userId));
						$userRow = $userQuery->fetch();
						if ($userRow->email)
						{
							$mailRecipients[$userRow->email] = $userRow->firstName . " " . $userRow->lastName;
						}
					}
				}
			}

			if (empty($groups))
			{
				$error = "Keine Gruppe ausgew&auml;hlt!";
			}
			else
			{
				$file = $_FILES["protocols_upload_file"];
				switch ($file["error"])
				{
					case UPLOAD_ERR_OK:
						$fileName = md5_file($file["tmp_name"]);
						if (move_uploaded_file($file["tmp_name"], UPLOAD_PATH . "/" . $fileName))
						{
							$query = Constants::$pdo->prepare("INSERT INTO `uploads` (`name`, `title`) VALUES(:name, :title)");
							$query->execute(array(":name" => $fileName, ":title" => $file["name"]));
							$uploadId = Constants::$pdo->lastInsertId();

							$query = Constants::$pdo->prepare("INSERT INTO `protocols` (`userId`, `uploadId`, `groups`, `date`, `name`) VALUES(:userId, :uploadId, :groups, :date, :name)");
							$query->execute(array(":userId" => $userData->id, ":uploadId" => $uploadId, ":groups" => implode(",", $groups), ":date" => $date[2] . "-" . $date[1] . "-" . $date[0], ":name" => $_POST["protocols_upload_name"]));

							if ($_POST["protocols_upload_sendmail"])
							{
								$replacements = array
								(
									"firstName" => $userData->firstName,
									"lastName" => $userData->lastName,
									"date" => $_POST["protocols_upload_date"],
									"name" => $_POST["protocols_upload_name"],
									"url" => BASE_URL . "/uploads/" . $uploadId . "/" . $fileName
								);

								$mail = new Mail("Protokoll hochgeladen", $replacements);
								$mail->setTemplate("protocol-uploaded");
								$mail->setTo($mailRecipients);
								$mail->setReplyTo(array($userData->email => $userData->firstName . " " . $userData->lastName));
								$mail->send();
							}

							echo "<div class='alert-success'>Das Protokoll wurde erfolgreich hochgeladen.</div>";

							$error = "";
						}
						break;
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$error = "Die ausgew&auml;hlte Datei ist zu Gro&szlig;!";
						break;
					case UPLOAD_ERR_NO_FILE:
						$error = "Es wurde keine Datei angegeben!";
						break;
				}
			}
		}
		else
		{
			$error = "Ung&uuml;ltiges Datum!";
		}

		if ($error)
		{
			echo "<div class='alert-error'>" . $error . "</div>";
		}
		else
		{
			header("Location: " . BASE_URL . "/" . implode("/", Constants::$pagePath));
			exit;
		}
	}

	echo "
		<fieldset id='protocols_upload'>
			<legend>Protokoll hochladen</legend>

			<form id='protocols_upload_form' action='/internalarea/protocols' method='post' enctype='multipart/form-data' onsubmit='protocols_confirmUpload(); return false;'>
				<label class='input-label' for='protocols_upload_date'>Datum:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='el-icon-calendar'></i></span>
					<input class='input-field date' type='text' id='protocols_upload_date' name='protocols_upload_date' placeholder='TT.MM.JJJJ'/>
				</div>

				<label class='input-label' for='protocols_upload_file'>Datei:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='el-icon-file'></i></span>
					<input class='input-field' type='file' id='protocols_upload_file' name='protocols_upload_file'/>
				</div>

				<label class='input-label' for='protocols_upload_name'>Name:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='el-icon-pencil'></i></span>
					<input class='input-field' type='text' id='protocols_upload_name' name='protocols_upload_name' placeholder='Titel oder Beschreibung' title='Ein beliebiger Titel oder eine Beschreibung von diesem Protokoll (Optional)'/>
				</div>

				<label class='input-label' for='protocols_upload_groups'>Gruppen:</label>
				<div id='protocols_upload_groups'>
	";
	foreach ($userGroups as $name => $title)
	{
		echo "<input type='checkbox' id='protocols_upload_groups_" . $name . "' name='protocols_upload_groups_" . $name . "'/><label for='protocols_upload_groups_" . $name . "'>" . escapeText($title) . "</label>";
	}
	echo "
				</div>
				
				<label class='input-label' for='protocols_upload_miscoptions'>Sonstige Optionen:</label>
				<div id='protocols_upload_miscoptions'>
					<input type='checkbox' id='protocols_upload_sendmail' name='protocols_upload_sendmail' value='1' checked='checked'/>
					<label for='protocols_upload_sendmail'>Email versenden</label>
				</div>
				
				<input type='hidden' id='protocols_upload_confirmed' name='protocols_upload_confirmed'/>

				<button type='submit'><i class='el-icon-upload'></i> Hochladen</button>
			</form>
		</fieldset>
	";
}

$protocols = array();

$query = Constants::$pdo->query("
	SELECT `uploadId`, `groups`, `date`, `protocols`.`name`, `uploads`.`name` AS `uploadName`
	FROM `protocols`
	LEFT JOIN `uploads` ON `uploads`.`id` = `protocols`.`uploadId`
");

while ($row = $query->fetch())
{
	$row->groups = explode(",", $row->groups);

	if (!Constants::$accountManager->hasPermissionInArray($row->groups, "protocols.view"))
	{
		continue;
	}

	$protocols[] = $row;
}

if (empty($protocols))
{
	echo "<div class='alert-error'>Keine Protokolle vorhanden!</div>";
}
else
{
	echo "
		<table class='table {sortlist: [[0,1]]}'>
			<thead>
				<tr>
					<th class='{sorter: \"number-attribute\"}'>Datum</th>
					<th>Name</th>
					<th>Berechtigungen</th>
				</tr>
			</thead>
			<tbody>
	";
	foreach ($protocols as $row)
	{
		$groupTitles = array();
		foreach ($row->groups as $group)
		{
			$groupTitles[] = escapeText($userGroups[$group]);
		}

		$date = strtotime($row->date);

		echo "
			<tr class='odd-even pointer' onclick='document.location=\"/uploads/" . $row->uploadId . "/" . $row->uploadName . "\";'>
				<td number='" . $date . "'>" . date("d.m.Y", $date) . "</td>
				<td>" . escapeText($row->name) . "</td>
				<td>" . implode(", ", $groupTitles) . "</td>
			</tr>
		";
	}
	echo "
			</tbody>
		</table>
	";
}
?>

<div id="protocols_upload_confirm" title="Protokoll hochladen">
	<p>Soll das ausgew&auml;hlte Protokoll jetzt hochgeladen werden?</p>

	<p>Das Protokoll wird f&uuml;r die folgenden Benutzergruppen sichtbar sein:</p>
	<ul id="protocols_upload_confirm_groups"></ul>
</div>

<script type="text/javascript">
	$("#protocols_upload_confirm").dialog(
	{
		closeText : "Schlie&szlig;en",
		resizable : false,
		modal : true,
		width : "auto",
		maxHeight : 500,
		autoOpen : false,
		buttons :
		{
			"Hochladen" : function ()
			{
				$("#protocols_upload_confirmed").val(true);
				document.getElementById("protocols_upload_form").submit();
			},
			"Abbrechen" : function ()
			{
				$(this).dialog("close");
			}
		}
	});

	function protocols_confirmUpload()
	{
		var groups = 0;
		$("#protocols_upload_confirm_groups").html("");
		$("#protocols_upload_groups").find("input:checkbox").each(function ()
		{
			if ($(this).is(":checked"))
			{
				groups++;
				$("#protocols_upload_confirm_groups").append("<li>" + $("label[for='" + $(this).attr("id") + "']").text() + "</li>");
			}
		});

		if ($("#protocols_upload_date").val())
		{
			if ($("#protocols_upload_file").val())
			{
				if (groups)
				{
					$("#protocols_upload_confirm").dialog("open");
				}
				else
				{
					alert("Es muss mindestens eine Gruppe ausgew\u00e4hlt sein!");
				}
			}
			else
			{
				alert("Keine Datei ausgew\u00e4hlt!");
			}
		}
		else
		{
			alert("Kein Datum ausgew\u00e4hlt!");
		}
	}
</script>