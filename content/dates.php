<?php
$year = Constants::$pagePath[1];
$month = Constants::$pagePath[2];

if (!$year)
{
	$year = date("Y");
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

$dates = Dates::getDates($year, $month);
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

if ($dates)
{
	echo "
		<table id='dates_table' class='table'>
			<thead>
				<tr>
					<th>Von</th>
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
		$startDateTime = array(getWeekdayName($weekday) . ", " . $startDate);
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
			$endDateTime = array(getWeekdayName($weekday) . ", " . $endDate);
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
			$location = "<a href='http://maps.google.com/maps/api/staticmap?center=" . $date->location->latitude . "," . $date->location->longitude . "&size=640x640&sensor=false&maptype=roadmap&zoom=17&markers=color:red|label:A|" . $date->location->latitude . "," . $date->location->longitude . "' rel='lightbox' title='" . $date->location->name . "'>" . $date->location->name . "</a>";
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
		
		<script type='text/javascript'>
			$('#dates_table').tablesorter(
			{
				headers :
				{
					0 :
					{
						sorter : 'number-attribute'
					}
				},
				sortList : [[0, 0]]
			});
		</script>
	";
}
else
{
	echo "<p><b>Es wurden keine Termine in dem ausgew&auml;hlten Jahr und Monat gefunden!</b></p>";
}
?>