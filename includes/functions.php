<?php
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

function getValidContentFile($path, $emptyNotFound)
{
	if (is_array($path))
	{
		$path = implode($path, "/");
	}
	$path = ROOT_PATH . "/content/" . $path;
	
	// home.php or subpage/mypage.php
	$fullPath = $path . ".php";
	if (file_exists($fullPath))
	{
		return $fullPath;
	}
	
	// home/index.php or subpage/mypage/index.php
	$fullPath = $path . "/index.php";
	if (file_exists($fullPath))
	{
		return $fullPath;
	}
	
	if (!$emptyNotFound)
	{
		return ROOT_PATH . "/includes/html/errorpage.php";
	}
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