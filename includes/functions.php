<?php
function convertLinebreaks($string)
{
	return str_replace(array("\r\n", "\r", "\n"), "\n", $string);
}

function escapeText($text)
{
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

function formatText($text)
{
	$text = escapeText($text);
	
	$find = array
	(
		"@\n@",
		"@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@is",
		"@[^<>\\\/[:space:]]+\@[^<>\\\/[:space:]]+@is",
		"/\[b\](.+?)\[\/b\]/is",
		"/\[hl\](.+?)\[\/hl\]/is",
		"/\[i\](.+?)\[\/i\]/is",
		"/\[u\](.+?)\[\/u\]/is"
	);
	
	$replace = array
	(
		"<br />",
		"<a href='\\0' target='_blank'>\\0</a>",
		"<a href='mailto:\\0' target='_blank'>\\0</a>",
		"<b>$1</b>",
		"<span class='highlight'>$1</span>",
		"<i>$1</i>",
		"<u>$1</u>",
		"<em>$1</em>"
	);
	
	$text = preg_replace($find, $replace, $text);
	
	return $text;
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

function getFileType($extension)
{
	switch (strtolower($extension))
	{
		case "doc":
			return "Word 97/2003 Dokument";
		case "docx":
			return "Word 2007+ Dokument";
		case "pdf":
			return "Adobe PDF Dokument";
		case "xls":
			return "Excel 97/2003 Arbeitsblatt";
		case "xlsx":
			return "Excel 2007+ Arbeitsblatt";
		default:
			return $extension . "-Datei";
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

function getWeekdayName($weekday, $long)
{
	if ($long)
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
	}
	else
	{
		$weekdays = array
		(
			"Mo",
			"Di",
			"Mi",
			"Do",
			"Fr",
			"Sa",
			"So"
		);
	}
	
	return $weekdays[$weekday - 1];
}

function resizeImage($sourceImage, $maxWidth, $maxHeight)
{
	$originalWidth = imageSX($sourceImage);
	$originalHeight = imageSY($sourceImage);
	
	if ($originalWidth <= $maxWidth and $originalHeight <= $maxHeight)
	{
		$resizedWidth = $originalWidth;
		$resizedHeight = $originalHeight;
	}
	else
	{
		$ratio = $maxWidth / $originalWidth;
		$resizedWidth = $maxWidth;
		$resizedHeight = $originalHeight * $ratio;
		
		if ($resizedHeight > $maxHeight)
		{
			$ratio = $maxHeight / $originalHeight;
			$resizedHeight = $maxHeight;
			$resizedWidth = $originalWidth * $ratio;
		}
	}
	
	$destinationImage = @imagecreatetruecolor($resizedWidth, $resizedHeight);
	if (!$destinationImage)
	{
		return false;
	}
	
	if (!imagecopyresampled($destinationImage, $sourceImage, 0, 0, 0, 0, $resizedWidth, $resizedHeight, $originalWidth, $originalHeight))
	{
		imagedestroy($destinationImage);
		return false;
	}
	
	return $destinationImage;
}

function setAdditionalHeader($header)
{
	if (defined("ADD_HTTP_HEADER"))
	{
		return false;
	}

	return define("ADD_HTTP_HEADER", $header);
}
?>