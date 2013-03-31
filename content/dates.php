<?php
$year = Constants::$pagePath[1];
$activeGroups = explode(" ", Constants::$pagePath[2]);

if (!$year)
{
	$year = date("Y");
}

$year = intval($year);
if (!$year)
{
	$year = null;
}

$title = "Termine";

$dates = Dates::getDates($year, $activeGroups);
if ($dates)
{
	$additionalTitle = array();
	
	if ($year)
	{
		$additionalTitle[] = date("Y", $dates[0]->startDate);
	}
	
	if (!empty($additionalTitle))
	{
		$title .= " - " . implode(" ", $additionalTitle);
	}
}

echo "<h1>" . $title . "</h1>";

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
}

if (!empty($userGroups))
{
	$group = new StdClass;
	$group->name = "public";
	$group->title = "&Ouml;ffentlich";
	array_unshift($userGroups, $group);
	
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
	if (Constants::$accountManager->getUserId())
	{
		$iCalendarUrl = BASE_URL . "/dates/internal.ics";
	}
	else
	{
		$iCalendarUrl = BASE_URL . "/dates/public.ics";
	}
	echo "
		<div class='no-print' id='dates_info_ics'>
			Diese Termine k&ouml;nnen im iCalendar-Format abgerufen werden, um sie in einer Kalenderanwendung wie z.B. Outlook, Google Kalender oder einer Kalender-App auf dem Smartphone anzuzeigen.<br />
			Einfach den folgenden Link in einer Kalenderanwendung einf&uuml;gen: <a href='" . $iCalendarUrl . "'>" . $iCalendarUrl . "</a>
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
		// Start date/time
		$dateString = getWeekdayName(date("N", $date->startDate), false) . " " . date("d.m.Y", $date->startDate);
		$timeString = date("H:i", $date->startDate);
		if ($date->endDate)
		{
			$endTime = date("H:i", $date->endDate);
		}
		else
		{
			$endTime = "";
		}
		if ($timeString == "00:00")
		{
			$timeString = "";
			$endTime = "";
		}
		else
		{
			if ($endTime and $endTime != "00:00")
			{
				$timeString .= " - " . $endTime;
			}
		}
		
		$rowClasses = array();
		if ($date->nextEvent)
		{
			$rowClasses[] = "table_highlight";
		}
		if ($date->bold)
		{
			$rowClasses[] = "bold";
		}
		
		$rowAttributes = array();
		
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
				<td number='" . $date->startDate . "' class='nowrap'>" . $dateString . "</td>
				<td number='" . $date->startDate . "' class='nowrap'>" . $timeString. "</td>
				<td>
					" . $date->title . "
		";
		if ($date->description)
		{
			echo "<p>" . formatText($date->description) . "</p>";
		}
		echo "
				</td>
				<td>" . $location . "</td>
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
	echo "<div class='error'>Es wurden keine Termine in der ausgew&auml;hlten Gruppe und dem ausgew&auml;hlten Jahr sowie Monat gefunden!</div>";
}
?>
<script type="text/javascript">
	function dates_applyGroups(form)
	{
		var groups = [];
		$(form).find("input:checkbox").each(function()
		{
			if ($(this).is(":checked"))
			{
				groups.push($(this).attr("group"));
			}
		});
		document.location.href = "/dates/<?php echo $year;?>/" + groups.join("+");
	}
</script>