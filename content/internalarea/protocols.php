<?php
$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups`");
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
		
		if ($_POST["protocols_upload_sendtoken"] == $userData->sendToken)
		{
			$date = explode(".", $_POST["protocols_upload_date"]);
			if (checkdate($date[1], $date[0], $date[2]))
			{
				$permissionQuery = Constants::$pdo->prepare("SELECT `userId` FROM `permissions` WHERE `permission` = :permission");
				$userQuery = Constants::$pdo->prepare("SELECT `email`, `firstName`, `lastName` FROM `users` WHERE `id` = :id");
				
				$mailRecipients = array();
				$groups = array();
				foreach ($_POST as $field => $value)
				{
					if (substr($field, 0, 24) == "protocols_upload_groups_" and $value)
					{
						$group = substr($field, 24);
						
						$groups[] = $group;
						
						$permissionQuery->execute(array
						(
							":permission" => "groups." . $group
						));
						while ($permissionRow = $permissionQuery->fetch())
						{
							$userQuery->execute(array
							(
								":id" => $permissionRow->userId
							));
							$userRow = $userQuery->fetch();
							$mailRecipients[$userRow->email] = $userRow->firstName . " " . $userRow->lastName;
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
								$query->execute(array
								(
									":name" => $fileName,
									":title" => $file["name"]
								));
								$uploadId = Constants::$pdo->lastInsertId();
								
								$query = Constants::$pdo->prepare("INSERT INTO `protocols` (`userId`, `uploadId`, `groups`, `date`, `name`) VALUES(:userId, :uploadId, :groups, :date, :name)");
								$query->execute(array
								(
									":userId" => $userData->id,
									":uploadId" => $uploadId,
									":groups" => implode(",", $groups),
									":date" => $date[2] . "-" . $date[1] . "-" . $date[0],
									":name" => $_POST["protocols_upload_name"]
								));
								
								$replacements = array
								(
									"FIRSTNAME" => $userData->firstName,
									"LASTNAME" => $userData->lastName,
									"DATE" => $_POST["protocols_upload_date"],
									"NAME" => $_POST["protocols_upload_name"],
									"URL" => BASE_URL . "/uploads/" . $uploadId . "/" . $fileName
								);
								$mail = new Mail("Protokoll hochgeladen", $replacements);
								$mail->setTemplate("protocol-uploaded");
								$mail->setTo($mailRecipients);
								$mail->setReplyTo(array($userData->email => $userData->firstName . " " . $userData->lastName));
								$mail->send();
								
								echo "<div class='ok'>Das Protokoll wurde erfolgreich hochgeladen.</div>";
								
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
		}
		else
		{
			$error = "Es wurde versucht, das Formular erneut abzuschicken!";
		}
		if ($error)
		{
			echo "<div class='error'>" . $error . "</div>";
		}
	}
	echo "
		<fieldset id='protocols_upload'>
			<legend>Protokoll hochladen</legend>
			
			<form id='protocols_upload_form' action='/internalarea/protocols' method='post' enctype='multipart/form-data' onsubmit='protocols_confirmUpload(); return false;'>
				<label for='protocols_upload_date'>Datum:</label>
				<input type='text' class='date' id='protocols_upload_date' name='protocols_upload_date'/>
				
				<label for='protocols_upload_file'>Datei:</label>
				<input type='file' id='protocols_upload_file' name='protocols_upload_file'/>
				
				<label for='protocols_upload_name'>Name:</label>
				<input type='text' id='protocols_upload_name' name='protocols_upload_name'/>
				
				<label for='protocols_upload_groups'>Gruppen:</label>
				<div id='protocols_upload_groups'>
	";
	foreach ($userGroups as $name => $title)
	{
		echo "<input type='checkbox' id='protocols_upload_groups_" . $name . "' name='protocols_upload_groups_" . $name . "'/><label for='protocols_upload_groups_" . $name . "'>" . $title . "</label>";
	}
	echo "
				</div>
				
				<input type='hidden' id='protocols_upload_confirmed' name='protocols_upload_confirmed'/>
				<input type='hidden' name='protocols_upload_sendtoken' value='" . Constants::$accountManager->getSendToken() . "'/>
				
				<input type='submit' value='Hochladen'/>
			</form>
		</fieldset>
	";
}

$protocols = array();

$query = Constants::$pdo->query("SELECT `uploadId`, `groups`, `date`, `protocols`.`name`, `uploads`.`name` AS `uploadName` FROM `protocols` LEFT JOIN `uploads` ON `uploads`.`id` = `protocols`.`uploadId`");
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
	echo "<div class='error'>Keine Protokolle vorhanden!</div>";
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
			$groupTitles[] = $userGroups[$group];
		}
		
		$date = strtotime($row->date);
		
		echo "
			<tr class='pointer' onclick='document.location=\"/uploads/" . $row->uploadId . "/" . $row->uploadName . "\";'>
				<td number='" . $date . "'>" . date("d.m.Y", $date) . "</td>
				<td>" . $row->name . "</td>
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
		resizable : false,
		modal : true,
		width : "auto",
		maxHeight : 500,
		autoOpen : false,
		buttons :
		{
			"Hochladen" : function()
			{
				$("#protocols_upload_confirmed").val(true);
				document.getElementById("protocols_upload_form").submit();
			},
			"Abbrechen" : function()
			{
				$(this).dialog("close");
			}
		}
	});
	
	function protocols_confirmUpload()
	{
		var groups = 0;
		$("#protocols_upload_confirm_groups").html("");
		$("#protocols_upload_groups input:checkbox").each(function()
		{
			if ($(this).is(":checked"))
			{
				groups++;
				$("#protocols_upload_confirm_groups").append("<li>" + $("label[for='" + $(this).attr("id") + "']").text()+ "</li>");
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
					alert(unescape("Es muss mindestens eine Gruppe ausgew%E4hlt sein!"));
				}
			}
			else
			{
				alert(unescape("Keine Datei ausgew%E4hlt!"));
			}
		}
		else
		{
			alert(unescape("Kein Datum ausgew%E4hlt!"));
		}
	}
</script>