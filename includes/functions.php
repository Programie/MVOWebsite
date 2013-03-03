<?php
function convertLinebreaks($string)
{
	return str_replace(array("\r\n", "\r", "\n"), "\n", $string);
}

function getCurrentSeason()
{
	$day = date("z");
	
	// Days of spring
	$spring_starts = date("z", strtotime("March 21"));
	$spring_ends = date("z", strtotime("June 20"));
	
	// Days of summer
	$summer_starts = date("z", strtotime("June 21"));
	$summer_ends = date("z", strtotime("September 22"));
	
	// Days of autumn
	$autumn_starts = date("z", strtotime("September 23"));
	$autumn_ends = date("z", strtotime("December 20"));
	
	if ($day >= $spring_starts and $day <= $spring_ends)
	{
		return "spring";
	}
	elseif ($day >= $summer_starts and $day <= $summer_ends)
	{
		return "summer";
	}
	elseif ($day >= $autumn_starts and $day <= $autumn_ends)
	{
		return "autumn";
	}
	else
	{
		return "winter";
	}
}

function getMonthName($month)
{
	$months = array
	(
		"Januar",
		"Februar",
		"M&auml;rz",
		"April",
		"Mai",
		"Juni",
		"Juli",
		"August",
		"September",
		"Oktober",
		"November",
		"Dezember"
	);
	
	return $months[$month - 1];
}

function getWeekdayName($weekday)
{
	$weekdays = array
	(
		"Montag",
		"Dienstag",
		"Mittwoch",
		"Donnerstag",
		"Freitag",
		"Samstag",
		"Sonntag"
	);
	
	return $weekdays[$weekday - 1];
}
?>