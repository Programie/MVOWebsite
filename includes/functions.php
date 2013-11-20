<?php
/**
 * Convert the following line breaks to Unix line breaks (LF):
 * CRLF (Windows)
 * CR (Old Mac)
 * LF (Unix)
 * @param string $string The string which should be converted
 * @return string A string containing only Unix line breaks (LF)
 */
function convertLinebreaks($string)
{
	return str_replace(array("\r\n", "\r", "\n"), "\n", $string);
}

/**
 * Escape the specified text using the htmlentities function
 * @param string $text The text which should be escaped
 * @return string The escaped text
 */
function escapeText($text)
{
	return htmlentities($text, ENT_COMPAT, "UTF-8");
}

/**
 * Format BBCode in text
 * @param string $text The text which should be formatted
 * @return string The formatted text
 */
function formatText($text)
{
	$text = escapeText($text);

	$find = array("@\n@", "@[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]@is", "@[^<>\\\/[:space:]]+\@[^<>\\\/[:space:]]+@is", "/\[b\](.+?)\[\/b\]/is", "/\[hl\](.+?)\[\/hl\]/is", "/\[i\](.+?)\[\/i\]/is", "/\[u\](.+?)\[\/u\]/is");

	$replace = array("<br />", "<a href='\\0' target='_blank'>\\0</a>", "<a href='mailto:\\0' target='_blank'>\\0</a>", "<b>$1</b>", "<span class='highlight'>$1</span>", "<i>$1</i>", "<span style='text-decoration: underline;'>$1</span>", "<em>$1</em>");

	$text = preg_replace($find, $replace, $text);

	return $text;
}

/**
 * Get the current season name
 * @return string A string containing one of the following season names:
 * <ul>
 *  <li>spring</li>
 *  <li>summer</li>
 *  <li>autumn</li>
 *  <li>winter</li>
 * </ul>
 */
function getCurrentSeason()
{
	$day = date("z");

	// Days of spring
	$spring_starts = date("z", strtotime("March 1"));
	$spring_ends = date("z", strtotime("June 20"));

	// Days of summer
	$summer_starts = date("z", strtotime("June 21"));
	$summer_ends = date("z", strtotime("September 22"));

	// Days of autumn
	$autumn_starts = date("z", strtotime("September 23"));
	$autumn_ends = date("z", strtotime("November 30"));

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

/**
 * Get the description of the specified file extension
 * @param string $extension One of the following extensions:
 * <ul>
 *  <li>doc</li>
 *  <li>docx</li>
 *  <li>pdf</li>
 *  <li>xls</li>
 *  <li>xlsx</li>
 * </ul>
 * @return string The description of the file extension
 */
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

/**
 * Get the name of the specified month
 * @param int $month The number of the month starting at 1 (1 = January, 2 = February, ...)
 * @return string The name of the month
 */
function getMonthName($month)
{
	$months = array("Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember");

	return $months[$month - 1];
}

/**
 * Get the name of the specified weekday
 * @param int $weekday The number of the specified weekday starting at 1 (1 = Monday, 2 = Tuesday, ...)
 * @param bool $long True to get the long name of the weekday, false to get the short name of the weekday
 * @return string The name of the weekday
 */
function getWeekdayName($weekday, $long)
{
	if ($long)
	{
		$weekdays = array("Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag", "Sonntag");
	}
	else
	{
		$weekdays = array("Mo", "Di", "Mi", "Do", "Fr", "Sa", "So");
	}

	return $weekdays[$weekday - 1];
}

/**
 * Resize the specified image to fit in the specified target size
 * The source image will not be modified, a copy is created instead!
 * @param resource $sourceImage The source image which should be resized
 * @param int $maxWidth The maximum width
 * @param int $maxHeight The maximum height
 * @return bool|resource The resized image or false if failed
 */
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

/**
 * Define the additional header which should be sent as soon as the page will be sent to the client
 * @param string $header The header which should be set (See the header() function for more details)
 * @return bool True if the additional header has been defined successfully, false if the header has already been set or define failed
 */
function setAdditionalHeader($header)
{
	if (defined("ADD_HTTP_HEADER"))
	{
		return false;
	}

	return define("ADD_HTTP_HEADER", $header);
}
?>