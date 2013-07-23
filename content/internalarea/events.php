<h1>Veranstaltungen</h1>

<?php
if (Constants::$accountManager->hasPermission("events.upload"))
{
	$years = array("");
	for ($year = date("Y") + 1; $year >= 2008; $year--)
	{
		$years[] = $year;
	}

	$events = array();
	$query = Constants::$pdo->query("SELECT `name`, `title` FROM `eventtypes` ORDER BY `title` ASC");
	while ($row = $query->fetch())
	{
		if (Constants::$accountManager->hasPermission("events.upload." . $row->name))
		{
			$events[$row->name] = $row->title;
		}
	}

	if ($_POST["events_upload_confirmed"])
	{
		$error = "Beim Hochladen ist ein Fehler aufgetreten! Bitte versuche es erneut oder wende dich an den Webmaster.";

		if ($events[$_POST["events_upload_event"]])
		{
			if ($_POST["events_upload_sendtoken"] == TokenManager::getSendToken("events_upload"))
			{
				$file = $_FILES["events_upload_file"];
				switch ($file["error"])
				{
					case UPLOAD_ERR_OK:
						$fileName = md5_file($file["tmp_name"]);
						if (move_uploaded_file($file["tmp_name"], UPLOAD_PATH . "/" . $fileName))
						{
							$query = Constants::$pdo->prepare("INSERT INTO `uploads` (`name`, `title`) VALUES(:name, :title)");
							$query->execute(array(":name" => $fileName, ":title" => $file["name"]));
							$uploadId = Constants::$pdo->lastInsertId();

							$query = Constants::$pdo->prepare("SELECT `id` FROM `eventtypes` WHERE `name` = :name");
							$query->execute(array(":name" => $_POST["events_upload_event"]));
							$row = $query->fetch();
							$typeId = $row->id;

							$year = $_POST["events_upload_year"];
							if (!$year)
							{
								$year = null;
							}

							$query = Constants::$pdo->prepare("INSERT INTO `events` (`typeId`, `year`, `userId`, `uploadId`) VALUES(:typeId, :year, :userId, :uploadId)");
							$query->execute(array(":typeId" => $typeId, ":year" => $year, ":userId" => Constants::$accountManager->getUserId(), ":uploadId" => $uploadId));

							echo "<p class='alert-success'>Die Datei wurde erfolgreich hochgeladen.</p>";

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
			else
			{
				$error = "Es wurde versucht, das Formular erneut abzuschicken!";
			}
		}
		else
		{
			$error = "Du hast nicht die notwendigen Rechte um eine Datei von dieser Veranstaltung hochzuladen!";
		}
		if ($error)
		{
			echo "<p class='alert-error'>" . $error . "</p>";
		}
	}

	echo "
		<fieldset>
			<legend>Datei hochladen</legend>
			
			<form id='events_upload_form' action='/internalarea/events' method='post' enctype='multipart/form-data' onsubmit='events_confirmUpload(); return false;'>
				<label class='input-label' for='events_upload_year'>Jahr:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='icon-calendar'></i></span>
					<select class='input-field' id='events_upload_year' name='events_upload_year'>
	";
	foreach ($years as $year)
	{
		$selected = "";
		if ($year == date("Y"))
		{
			$selected = "selected='selected'";
		}
		$yearTitle = $year;
		if (!$yearTitle)
		{
			$yearTitle = "Planung";
		}
		echo "<option value='" . $year . "' " . $selected . ">" . $yearTitle . "</option>";
	}
	echo "
					</select>
				</div>
				
				<label class='input-label' for='events_upload_event'>Veranstaltung:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='icon-star'></i></span>
					<select class='input-field' id='events_upload_event' name='events_upload_event'>
	";
	foreach ($events as $name => $title)
	{
		echo "<option value='" . $name . "'>" . $title . "</option>";
	}
	echo "
					</select>
				</div>
				
				<label class='input-label' for='events_upload_file'>Datei:</label>
				<div class='input-container'>
					<span class='input-addon'><i class='icon-file'></i></span>
					<input class='input-field' type='file' id='events_upload_file' name='events_upload_file'/>
				</div>
				
				<input type='hidden' id='events_upload_confirmed' name='events_upload_confirmed'/>
				<input type='hidden' name='events_upload_sendtoken' value='" . TokenManager::getSendToken("events_upload", true) . "'/>
				
				<input type='submit' value='Hochladen'/>
			</form>
		</fieldset>
	";
}

$years = array();
$query = Constants::$pdo->query("
	SELECT `year`, `eventtypes`.`name` AS `eventType`, `eventtypes`.`title` AS `eventTypeTitle`, `events`.`uploadId`, `uploads`.`name` AS `uploadName`, `uploads`.`title` AS `uploadTitle`
	FROM `events`
	LEFT JOIN `eventtypes` ON `eventtypes`.`id` = `events`.`typeId`
	LEFT JOIN `uploads` ON `uploads`.`id` = `events`.`uploadId`
");
while ($row = $query->fetch())
{
	if (Constants::$accountManager->hasPermission("events.view." . $row->eventType))
	{
		if (!$row->year)
		{
			$row->year = "";
		}
		$years[$row->year][$row->eventTypeTitle][] = $row;
	}
}

if (empty($years))
{
	echo "<p class='alert-error'>Keine Veranstaltungen vorhanden!</p>";
}
else
{
	uksort($years, function ($item1, $item2)
	{
		if (!$item1 and $item2)
		{
			return -1;
		}
		if ($item1 and !$item2)
		{
			return 1;
		}

		return $item1 < $item2;
	});

	echo "<ul>";
	foreach ($years as $year => $eventTypes)
	{
		if (!$year)
		{
			$year = "Planung";
		}
		echo "
			<li>
				<b>" . $year . "</b>
				<ul>
		";
		ksort($eventTypes);
		foreach ($eventTypes as $eventType => $events)
		{
			echo "
				<li>
					<b>" . $eventType . "</b>
					<ul>
			";
			uasort($events, function ($item1, $item2)
			{
				return $item1->uploadTitle > $item2->uploadTitle;
			});
			foreach ($events as $event)
			{
				echo "<li><a href='/uploads/" . $event->uploadId . "/" . $event->uploadName . "'>" . escapeText($event->uploadTitle) . "</a></li>";
			}
			echo "
					</ul>
				</li>
			";
		}
		echo "
				</ul>
			</li>
		";
	}
}
?>

<div id="events_upload_confirm" title="Datei hochladen">
	<p>Soll die ausgew&auml;hlte Datei nun hochgeladen werden?</p>

	<p>
		<p><b>Jahr:</b> <span id="events_upload_confirm_year"/></p>

		<p><b>Veranstaltung:</b> <span id="events_upload_confirm_event"/></p>
	</p>
</div>

<script type="text/javascript">
	$("#events_upload_confirm").dialog(
	{
		resizable: false,
		modal: true,
		width: "auto",
		autoOpen: false,
		buttons: {
			"Hochladen": function ()
			{
				$("#events_upload_confirmed").val(true);
				document.getElementById("events_upload_form").submit();
			},
			"Abbrechen": function ()
			{
				$(this).dialog("close");
			}
		}
	});

	function events_confirmUpload()
	{
		if ($("#events_upload_year").val() == null)
		{
			alert("Kein Jahr ausgew\u00e4lt!");
		}
		else
		{
			if ($("#events_upload_event").val() == null)
			{
				alert("Keine Veranstaltung ausgew\u00e4hlt!");
			}
			else
			{
				if ($("#events_upload_file").val())
				{
					$("#events_upload_confirm_year").html($("#events_upload_year").find(":selected").text());
					$("#events_upload_confirm_event").html($("#events_upload_event").find(":selected").text());
					$("#events_upload_confirm").dialog("open");
				}
				else
				{
					alert("Keine Datei ausgew\u00e4hlt!");
				}
			}
		}
	}
</script>