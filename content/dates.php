<?php
$year = Constants::$pagePath[1];
$month = Constants::$pagePath[2];
$group = Constants::$pagePath[3];

if (!$year)
{
	$year = date("Y");
}

if ($group == "all")
{
	$group = null;
}

$year = intval($year);
if (!$year)
{
	$year = null;
}

$month = intval($month);
if (!$month)
{
	$month = null;
}

$title = "Termine";

$dates = Dates::getDates($year, $month, $group);
if ($dates)
{
	$additionalTitle = array();
	
	if ($month)
	{
		$additionalTitle[] = getMonthName($month);
	}
	
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
	$group1 = new StdClass;
	$group1->name = "all";
	$group1->title = "Alle";
	
	$group2 = new StdClass;
	$group2->name = "public";
	$group2->title = "&Ouml;ffentlich";
	array_unshift($userGroups, $group1, $group2);
	
	$activeGroup = "all";
	
	foreach ($userGroups as $group)
	{
		if ($group->name == Constants::$pagePath[3])
		{
			$activeGroup = $group->name;
		}
	}
	
	echo "
		<fieldset id='dates_groups'>
			<legend>Gruppen</legend>
	";
	foreach ($userGroups as $group)
	{
		$buttonStyle = "";
		if ($group->name == $activeGroup)
		{
			$buttonStyle = "style='font-weight: bold;'";
		}
		echo "<a href='/dates/" . $year . "/" . ($month ? $month : "all") . "/" . $group->name . "'><button type='button' " . $buttonStyle . ">" . $group->title . "</button></a>";
	}
	echo "</fieldset>";
}

if ($dates)
{
	echo "
		<table id='dates_table' class='table tablesorter {sortlist: [[0,0]]}'>
			<thead>
				<tr>
					<th class='{sorter: \"number-attribute\"}'>Von</th>
					<th>Bis</th>
					<th>Veranstaltung</th>
					<th>Ort</th>
				</tr>
			</thead>
			<tbody>
	";
	foreach ($dates as $date)
	{
		// Start date/time
		$weekday = date("N", $date->startDate);
		$startDate = date("d.m.Y", $date->startDate);
		$startDateTime = array(getWeekdayName($weekday, false) . " " . $startDate);
		$startTime = date("H:i", $date->startDate);
		if ($startTime != "00:00")
		{
			$startDateTime[] = $startTime . " Uhr";
		}
		
		// End date/time
		$weekday = date("N", $date->endDate);
		$endDate = date("d.m.Y", $date->endDate);
		if ($endDate == "01.01.1970")
		{
			$endDateTime = array();
		}
		else
		{
			$endDateTime = array();
			if ($startDate != $endDate)
			{
				$endDateTime[] = getWeekdayName($weekday, false) . " " . $endDate;
			}
			$endTime = date("H:i", $date->endDate);
			if ($endTime != "00:00")
			{
				$endDateTime[] = $endTime . " Uhr";
			}
		}
		
		$rowClasses = array();
		if ($date->nextEvent)
		{
			$rowClasses[] = "table_highlight";
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
				<td number='" . $date->startDate . "' class='nowrap'>" . implode("<br />", $startDateTime) . "</td>
				<td number='" . $date->endDate . "' class='nowrap'>" . implode("<br />", $endDateTime) . "</td>
				<td>" . $date->title . "</td>
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