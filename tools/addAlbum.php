<?php
define("ROOT_PATH", __DIR__ . "/..");
define("PICTURES_PATH", ROOT_PATH . "/files/pictures");

require_once ROOT_PATH . "/includes/config.inc.php";
require_once ROOT_PATH . "/includes/Constants.class.php";
require_once ROOT_PATH . "/includes/database.php";
require_once ROOT_PATH . "/includes/Pictures.class.php";

if (php_sapi_name() != "cli")
{
	die("This script can only be invoked via the CLI!");
}

$albumFolderName = @$argv[1];
if (!$albumFolderName)
{
	die("Usage: " . $argv[0] . " <album folder name>");
}

$path = PICTURES_PATH . "/" . $albumFolderName;
$albumFile = $path . "/album.json";

if (!file_exists($albumFile))
{
	die("No album.json found in '" . $path . "'!");
}

$albumData = json_decode(file_get_contents($albumFile));

/*
 * Expected JSON structure:
 *
 * {
 *     "id" : <id-in-picturealbums-table>,
 *     "year" : 2014,
 *     "album" : "01.01 The title of the album",
 *     "pictures" :
 *     [
 *         "md5-of-original-file-1",
 *         "md5-of-original-file-2",
 *         "md5-of-original-file-3"
 *     ]
 * }
 */

$albumId = $albumData->id;
$year = $albumData->year;

// Insert as a new album if no albumId specified
if (!$albumId)
{
	$folderName = $albumData->album;
	if (preg_match("/^([0-9][0-9]).([0-9][0-9])([0-9\.\-]+)? ?(.*)/", $folderName, $matches))
	{
		$month = $matches[1];
		$day = $matches[2];
		$title = trim($matches[4]);
	}
	else
	{
		$month = 1;
		$day = 1;
		$title = $folderName;
	}

	$query = Constants::$pdo->prepare("
		INSERT INTO `picturealbums`
		(`published`, `date`, `isPublic`, `coverPicture`, `title`, `text`)
		VALUES(0, :date, 0, 1, :title, '')
	");
	$query->execute(array
	(
		":date" => $year . "-" . $month . "-" . $day,
		":title" => $title
	));

	$albumId = Constants::$pdo->lastInsertId();

	$albumData->id = $albumId;

	file_put_contents($albumFile, json_encode($albumData, JSON_PRETTY_PRINT));
}

// Read pictures
$pictures = array();
foreach (array_values($albumData->pictures) as $index => $picture)
{
	$pictures[$picture] = array
	(
		"number" => $index + 1,
		"id" => 0
	);
}

// Get old pictures
$deleteQuery = Constants::$pdo->prepare("DELETE FROM `pictures` WHERE `id` = :id");
$query = Constants::$pdo->prepare("SELECT `id`, `fileId` FROM `pictures` WHERE `albumId` = :albumId");
$query->execute(array
(
	":albumId" => $albumId
));
while ($row = $query->fetch())
{
	if ($pictures[$row->fileId])
	{
		$pictures[$row->fileId]["id"] = $row->id;
	}
	else
	{
		// Delete if no longer existing
		$deleteQuery->execute(array
		(
			":id" => $row->id
		));
	}
}

// Insert and update pictures
$insertQuery = Constants::$pdo->prepare("INSERT INTO `pictures` (`albumId`, `fileId`, `number`, `text`) VALUES(:albumId, :fileId, :number, '')");
$updateQuery = Constants::$pdo->prepare("UPDATE `pictures` SET `number` = :number WHERE `id` = :id");
foreach ($pictures as $fileId => $picture)
{
	if ($picture["id"])
	{
		$updateQuery->execute(array
		(
			":number" => $picture["number"],
			":id" => $picture["id"]
		));
	}
	else
	{
		$insertQuery->execute(array
		(
			":albumId" => $albumId,
			":fileId" => $fileId,
			":number" => $picture["number"]
		));
	}
}

// Set this album as the album of the year if no album has been set yet
Pictures::updateAlbumOfTheYear($year, $albumId, false);

// Move the folder of this album if the name is not the id
if ($albumFolderName != $albumId)
{
	rename($path, PICTURES_PATH . "/" . $albumId);
}

// Return album ID (Required by PHP client script)
echo $albumId;
?>