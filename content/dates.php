<?php
$year = Constants::$pagePath[1];
$activeGroups = explode(" ", Constants::$pagePath[2]);

$year = Dates::convertYear($year);

$title = array("Termine");

$dates = Dates::getDates($year, $activeGroups);
if ($dates)
{
	$yearText = Dates::getYearText($year);
	if ($yearText)
	{
		$title[] = $yearText;
	}
}

if ($year == "current")
{
	$title = array("Unsere n&auml;chsten Termine");
}

echo "<h1>" . implode(" - ", $title) . "</h1>";

$userGroups = array();

if (Constants::$accountManager->getUserId())
{
	$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups`");
	while ($row = $query->fetch())
	{
		if (Constants::$accountManager->hasPermission("dates." . $row->name))
		{
			$userGroups[] = $row;
		}
	}

	if (Constants::$accountManager->hasPermission("dates.edit"))
	{
		if (isset($_POST["dates_edit_id"]))
		{
			if ($_POST["dates_edit_sendtoken"] == TokenManager::getSendToken("dates_edit"))
			{
				$id = intval($_POST["dates_edit_id"]);
				if ($id and $_POST["dates_edit_hide"])
				{
					$query = Constants::$pdo->prepare("UPDATE `dates` SET `enabled` = '0' WHERE `id` = :id");
					$query->execute(array(":id" => $_POST["dates_edit_id"]));
					$dates = Dates::getDates($year, $activeGroups);
					echo "<div class='ok'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				}
				else
				{
					$date = explode(".", $_POST["dates_edit_date"]);
					if (@checkdate($date[1], $date[0], $date[2]))
					{
						$locationId = null;
						if ($_POST["dates_edit_location"])
						{
							$query = Constants::$pdo->prepare("SELECT `id` FROM `locations` WHERE `name` = :name");
							$query->execute(array(":name" => $_POST["dates_edit_location"]));
							$row = $query->fetch();
							$locationId = $row->id;
							if (!$locationId)
							{
								$query = Constants::$pdo->prepare("INSERT INTO `locations` (`name`) VALUES(:name)");
								$query->execute(array(":name" => $_POST["dates_edit_location"]));
								$locationId = Constants::$pdo->lastInsertId();
							}
						}

						$startTime = $_POST["dates_edit_time_start"];
						$endTime = $_POST["dates_edit_time_end"];

						$endDate = null;
						if ($startTime and $endTime)
						{
							$endDate = $date[2] . "-" . $date[1] . "-" . $date[0] . " " . $endTime;
						}
						$groups = array();
						foreach ($_POST as $key => $value)
						{
							if (substr($key, 0, 18) == "dates_edit_groups_")
							{
								$groups[] = substr($key, 18);
							}
						}
						if (empty($groups))
						{
							$groups[] = "public";
						}

						$queryData = array(":startDate" => $date[2] . "-" . $date[1] . "-" . $date[0] . " " . $startTime, ":endDate" => $endDate, ":groups" => implode(",", $groups), ":title" => $_POST["dates_edit_title"], ":description" => $_POST["dates_edit_description"], ":locationId" => $locationId, ":showInAttendanceList" => !!$_POST["dates_edit_options_showinattendancelist"], ":bold" => !!$_POST["dates_edit_options_bold"]);
						if ($id)
						{
							$query = Constants::$pdo->prepare("
								UPDATE `dates`
								SET
									`startDate` = :startDate,
									`endDate` = :endDate,
									`groups` = :groups,
									`title` = :title,
									`description` = :description,
									`locationId` = :locationId,
									`showInAttendanceList` = :showInAttendanceList,
									`bold` = :bold
								WHERE `id` = :id
							");
							$queryData["id"] = $id;
						}
						else
						{
							$query = Constants::$pdo->prepare("
								INSERT INTO `dates` (`startDate`, `enddate`, `groups`, `title`, `description`, `locationId`, `showInAttendanceList`, `bold`)
								VALUES(:startDate, :endDate, :groups, :title, :description, :locationId, :showInAttendanceList, :bold)
							");
						}
						$query->execute($queryData);
						$dates = Dates::getDates($year, $activeGroups);
						echo "<div class='ok'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
					}
					else
					{
						echo "<div class='error'>Das eingegebene Datum ist ung&uuml;ltig!</div>";
					}
				}
			}
			else
			{
				echo "<div class='error'>Es wurde versucht, die &Auml;nderungen erneut zu &uuml;bernehmen!</div>";
			}
		}
		echo "<button type='button' id='dates_add_button'>Neuer Termin</button>";
	}
}

if (!empty($userGroups))
{
	foreach ($userGroups as $index => $group)
	{
		if ($group->name != "all")
		{
			if (!Dates::getDates($year, array($group->name)))
			{
				unset($userGroups[$index]);
				continue;
			}
		}
		if (!$activeGroups[0] or in_array($group->name, $activeGroups))
		{
			$group->active = true;
		}
	}

	echo "
		<fieldset class='no-print' id='dates_groups'>
			<legend>Gruppen</legend>
			<form onsubmit='dates_applyGroups(this); return false;'>
	";
	foreach ($userGroups as $group)
	{
		$checked = "";

		if ($group->active)
		{
			$checked = "checked='checked'";
		}
		echo "<input type='checkbox' id='dates_groups_" . $group->name . "' group='" . $group->name . "' " . $checked . "/><label for='dates_groups_" . $group->name . "'>" . $group->title . "</label>";
	}
	echo "
				<input type='submit' value='OK'/>
			</form>
		</fieldset>
	";
}

if ($dates)
{
	$newPagePath = Constants::$pagePath;
	$newPagePath[1] = $year . ".pdf";
	$pdfUrl = BASE_URL . "/" . implode("/", $newPagePath);
	$iCalendarToken = "";
	if (Constants::$accountManager->getUserId())
	{
		$iCalendarToken = Constants::$accountManager->getCalendarToken();
		$iCalendarUrl = BASE_URL . "/dates/internal.ics";
		$iCalendarTokenUrl = $iCalendarUrl . "/" . $iCalendarToken;
	}
	else
	{
		$iCalendarUrl = BASE_URL . "/dates/public.ics";
	}
	echo "
		<div id='dates_tabs' class='no-print'>
			<ul>
				<li><a href='#dates_tabs_pdf'>PDF</a></li>
				<li><a href='#dates_tabs_ics'>iCalendar</a></li>
			</ul>
			<div id='dates_tabs_pdf'>
				Mit dem folgenden Link k&ouml;nnen diese Termine als PDF Dokument heruntergeladen werden: <a href='" . $pdfUrl . "' target='_blank'>" . $pdfUrl . "</a>
			</div>
			<div id='dates_tabs_ics'>
				Diese Termine k&ouml;nnen im iCalendar-Format abgerufen werden, um sie in einer Kalenderanwendung wie z.B. Outlook oder einer Kalender-App auf dem Smartphone anzuzeigen.<br />
				Folgenden Link in der Kalenderanwendung einf&uuml;gen: <a href='" . $iCalendarUrl . "' onclick='prompt(\"Tragen Sie die folgende Adresse in der Kalenderanwendung ein:\", \"" . $iCalendarUrl . "\"); return false;'>" . $iCalendarUrl . "</a>.
	";
	if ($iCalendarToken)
	{
		echo "<p>Sollte die verwendete Kalenderanwendung keine Authentifizierung unterst&uuml;tzen (z.B. Google Kalender), bitte den folgenden Link verwenden: <a href='" . $iCalendarTokenUrl . "' onclick='prompt(\"Tragen Sie die folgende Adresse in der Kalenderanwendung ein:\", \"" . $iCalendarTokenUrl . "\"); return false;'>" . $iCalendarTokenUrl . "</a></p>";
	}
	echo "
			</div>
		</div>
		<table id='dates_table' class='table tablesorter {sortlist: [[0,0]]}'>
			<thead>
				<tr>
					<th class='{sorter: \"number-attribute\"}'>Datum</th>
					<th class='{sorter: \"number-attribute\"}'>Zeit</th>
					<th>Veranstaltung</th>
					<th>Ort</th>
				</tr>
			</thead>
			<tbody>
	";
	foreach ($dates as $date)
	{
		$rowClasses = array();
		if ($date->nextEvent)
		{
			$rowClasses[] = "table_highlight";
		}
		if ($date->bold)
		{
			$rowClasses[] = "bold";
		}
		if ($date->showInAttendanceList)
		{
			$rowClasses[] = "dates_showinattendancelist";
		}

		$rowAttributes = array("dateid='" . $date->id . "'", "startdate='" . $date->startDate . "'");
		if ($date->endDate)
		{
			$rowAttributes[] = "enddate='" . $date->endDate . "'";
		}
		if ($date->groups and $date->groups[0])
		{
			$rowAttributes[] = "groups='" . implode(" ", $date->groups) . "'";
		}

		if (!empty($rowClasses))
		{
			$rowAttributes[] = "class='" . implode(" ", $rowClasses) . "'";
		}

		if ($date->location->latitude and $date->location->longitude)
		{
			$location = "<a class='colorbox-iframe' href='http://maps.google.com/maps?f=q&amp;q=loc:" . $date->location->latitude . "," . $date->location->longitude . "&amp;z=17&amp;iwloc=near&amp;output=embed' title='" . $date->location->name . "'>" . $date->location->name . "</a>";
		}
		else
		{
			$location = $date->location->name;
		}

		echo "
			<tr " . implode(" ", $rowAttributes) . ">
				<td number='" . $date->startDate . "' class='nowrap'>" . Dates::getDateText($date->startDate) . "</td>
				<td number='" . $date->startDate . "' class='nowrap'>" . Dates::getTimeText($date->startDate, $date->endDate) . "</td>
				<td class='dates_titledescription'>
					<span>" . $date->title . "</span>
		";
		if ($date->description)
		{
			echo "<p>" . formatText($date->description) . "</p>";
		}
		echo "
				</td>
				<td class='dates_location'>" . $location . "</td>
			</tr>
		";
	}
	echo "
			</tbody>
		</table>
		
	";
}
else
{
	if (empty($userGroups))
	{
		echo "<div class='error'>Es sind keine Termine in dem ausgew&auml;hlten Jahr vorhanden!</div>";
	}
	else
	{
		echo "<div class='error'>Es sind keine Termine in den ausgew&auml;hlten Gruppen sowie dem ausgew&auml;hlten Jahr vorhanden!</div>";
	}
}

if (Constants::$accountManager->hasPermission("dates.edit"))
{
	$sendToken = TokenManager::getSendToken("dates_edit", true);
	echo "
		<div id='dates_hide'>
			<p>Soll der ausgew&auml;hlte Termin wirklich ausgeblendet werden?</p>
			
			<table>
				<tr>
					<td><b>Datum:</b></td>
					<td><span></span></td>
				</tr>
				<tr>
					<td><b>Zeit:</b></td>
					<td><span></span></td>
				</tr>
				<tr>
					<td><b>Veranstaltung:</b></td>
					<td><span></span></td>
				</tr>
				<tr>
					<td><b>Ort:</b></td>
					<td><span></span></td>
				</tr>
			</table>
			
			<p><b>Hinweis:</b> Der Termin kann nur &uuml;ber die Datenbank wiederhergestellt werden!</p>
			
			<form id='dates_hide_form' method='post' onsubmit='return false'>
				<input type='hidden' id='dates_edit_sendtoken' name='dates_edit_sendtoken' value='" . $sendToken . "'/>
				<input type='hidden' id='dates_hide_id' name='dates_edit_id'/>
				<input type='hidden' id='dates_edit_hide' name='dates_edit_hide' value='1'/>
			</form>
		</div>
		
		<div id='dates_edit'>
			<form id='dates_edit_form' method='post' onsubmit='return false'>
				<label for='dates_edit_title'>Titel</label>
				<input type='text' id='dates_edit_title' name='dates_edit_title' placeholder='Titel von diesem Termin'/>
				
				<label for='dates_edit_date'>Datum</label>
				<input type='text' id='dates_edit_date' name='dates_edit_date' class='date' placeholder='TT.MM.JJJJ'/>
				
				<label for='dates_edit_time_start'>Zeit</label>
				<div>
					<input type='text' class='time' id='dates_edit_time_start' name='dates_edit_time_start' placeholder='HH:MM'/>
					<span>bis</span>
					<input type='text' class='time' id='dates_edit_time_end' name='dates_edit_time_end' placeholder='HH:MM'/>
				</div>
				
				<label for='dates_edit_location'>Ort</label>
				<input type='text' id='dates_edit_location' name='dates_edit_location' placeholder='Ort, an welchem dieser Termin stattfindet'/>
				
				<label for='dates_edit_description'>Beschreibung</label>
				<textarea id='dates_edit_description' name='dates_edit_description' rows='10' cols='10' placeholder='Weitere Beschreibung von diesem Termin'></textarea>
				
				<fieldset id='dates_edit_groups'>
					<legend>Gruppen</legend>
	";
	$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups`");
	while ($row = $query->fetch())
	{
		echo "<div><input type='checkbox' id='dates_edit_groups_" . $row->name . "' name='dates_edit_groups_" . $row->name . "'/><label for='dates_edit_groups_" . $row->name . "'>" . $row->title . "</label></div>";
	}
	echo "
				</fieldset>
				
				<fieldset id='dates_edit_options'>
					<legend>Optionen</legend>
					
					<div><input type='checkbox' id='dates_edit_options_showinattendancelist' name='dates_edit_options_showinattendancelist'/><label for='dates_edit_options_showinattendancelist'>In Anwesenheitsliste anzeigen</label></div>
					<div><input type='checkbox' id='dates_edit_options_bold' name='dates_edit_options_bold'/><label for='dates_edit_options_bold'>Fett</label></div>
				</fieldset>
				
				<input type='hidden' id='dates_edit_sendtoken' name='dates_edit_sendtoken' value='" . $sendToken . "'/>
				<input type='hidden' id='dates_edit_id' name='dates_edit_id'/>
			</form>
		</div>
		
		<div id='dates_edit_contextmenu'>
			<ul>
				<li id='dates_edit_contextmenu_edit'><img src='/files/images/contextmenu/edit.png'/> Bearbeiten</li>
				<li id='dates_edit_contextmenu_hide'><img src='/files/images/contextmenu/trash.png'/> Ausblenden</li>
			</ul>
		</div>
	";
}
?>
<script type="text/javascript">
	function dates_applyGroups(form)
	{
		var groups = [];
		$(form).find("input:checkbox").each(function ()
		{
			if ($(this).is(":checked"))
			{
				groups.push($(this).attr("group"));
			}
		});
		document.location.href = "/dates/<?php echo $year;?>/" + groups.join("+");
	}

	$("#dates_tabs").tabs();

<?php
if (Constants::$accountManager->hasPermission("dates.edit"))
{
	$locations = array();
	$query = Constants::$pdo->query("SELECT `name` FROM `locations`");
	while ($row = $query->fetch())
	{
		$locations[] = $row->name;
	}
?>

	$("#dates_edit_location").autocomplete(
	{
		source: <?php echo json_encode($locations);?>
	});

	$("#dates_hide").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		modal: true,
		resizable: false,
		title: "Termin ausblenden",
		width: "auto",
		buttons:
		{
			"OK": function ()
			{
				$("#dates_hide_form")[0].submit();
			},
			"Abbrechen": function ()
			{
				$(this).dialog("close");
			}
		}
	});

	$("#dates_edit").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		height: 600,
		minWidth: 500,
		modal: true,
		width: 800,
		buttons:
		{
			"OK": function ()
			{
				if ($("#dates_edit_title").val())
				{
					if ($("#dates_edit_date").val())
					{
						$("#dates_edit_form")[0].submit();
					}
					else
					{
						alert("Kein Datum angegeben!");
					}
				}
				else
				{
					alert("Kein Titel angegeben!");
				}
			},
			"Abbrechen": function ()
			{
				$(this).dialog("close");
			}
		}
	});

	$("#dates_add_button").click(function ()
	{
		$("#dates_edit_form")[0].reset();
		$("#dates_edit_id").val(0);
		$("#dates_edit").dialog("option", "title", $("#dates_add_button").text());
		$("#dates_edit").dialog("open");
	});

	$("#dates_table tbody tr").contextMenu("dates_edit_contextmenu",
	{
		bindings:
		{
			dates_edit_contextmenu_edit: function (trigger)
			{
				$("#dates_edit_form")[0].reset();

				var startDate = new Date($(trigger).attr("startdate") * 1000);
				var endDate = new Date($(trigger).attr("enddate") * 1000);
				var startTime;
				var endTime;
				if (startDate && (startDate.getHours() || startDate.getMinutes()))
				{
					startTime = ("0" + startDate.getHours()).slice(-2) + ":" + ("0" + startDate.getMinutes()).slice(-2);
				}
				if (endDate && (endDate.getHours() || endDate.getMinutes()))
				{
					endTime = ("0" + endDate.getHours()).slice(-2) + ":" + ("0" + endDate.getMinutes()).slice(-2);
				}

				var groups = $(trigger).attr("groups");
				if (groups)
				{
					groups = groups.split(" ");
					for (var group in groups)
					{
						$("#dates_edit_groups_" + groups[group]).prop("checked", true);
					}
				}

				$("#dates_edit_id").val($(trigger).attr("dateid"));
				$("#dates_edit_date").datepicker("setDate", startDate);
				$("#dates_edit_time_start").val(startTime);
				$("#dates_edit_time_end").val(endTime);
				$("#dates_edit_title").val($($(trigger).find("td.dates_titledescription span")[0]).text());
				$("#dates_edit_description").val($($(trigger).find("td.dates_titledescription p")[0]).text());
				$("#dates_edit_location").val($(trigger).find("td.dates_location").text());
				$("#dates_edit_options_bold").prop("checked", $(trigger).hasClass("bold"));
				$("#dates_edit_options_showinattendancelist").prop("checked", $(trigger).hasClass("dates_showinattendancelist"));
				$("#dates_edit").dialog("option", "title", "Termin bearbeiten");
				$("#dates_edit").dialog("open");
			},

			dates_edit_contextmenu_hide: function (trigger)
			{
				var column = 0;
				var confirmTableCells = $("#dates_hide table td span");
				$(trigger).find("td").each(function ()
				{
					if ($(this).children().length > 0)
					{
						$(confirmTableCells[column]).text($($(this).children()[0]).text());
					}
					else
					{
						$(confirmTableCells[column]).text($(this).text());
					}
					column++;
				});
				$("#dates_hide_id").val($(trigger).attr("dateid"));
				$("#dates_hide").dialog("open");
			}
		}
	});
<?php
}
?>
</script>