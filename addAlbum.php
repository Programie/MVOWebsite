<?php
define("ROOT_PATH", __DIR__);
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
$albumXmlFile = $path . "/album.xml";

if (!file_exists($albumXmlFile))
{
	die("No album.xml found in '" . $path . "'!");
}

/*
 * Expected XML structure:
 *
 * <?xml version="1.0" encoding="utf-8"?>
 * <album id="id-in-picturealbums-table"><!-- id can be set to 0 or omitted to insert a new album instead of updating an existing one -->
 *   <year>2013</year>
 *   <foldername>01.01 The title of the album</foldername>
 *   <pictures>
 *     <picture name="md5-of-original-file-1" number="1"/>
 *     <picture name="md5-of-original-file-2" number="2"/>
 *     <picture name="md5-of-original-file-3" number="3"/>
 *     <!-- More pictures -->
 *   </pictures>
 * </album>
 */

$document = new DOMDocument;
$document->load($albumXmlFile);
$root = $document->getElementsByTagName("album")->item(0);

$albumId = $root->getAttribute("id");
$year = $root->getElementsByTagName("year")->item(0)->nodeValue;

// Insert as a new album if no albumId specified
if (!$albumId)
{
	$folderName = $root->getElementsByTagName("foldername")->item(0)->nodeValue;
	if (preg_match("/^([0-9][0-9]).([0-9][0-9])(.*?) (.*)/", $folderName, $matches))
	{
		$month = $matches[1];
		$day = $matches[2];
		$title = trim($matches[3]);
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
	$root->setAttribute("id", $albumId);
	$document->save($albumXmlFile);
}

// Read pictures from XML
$pictureElements = $root->getElementsByTagName("pictures")->item(0)->getElementsByTagName("picture");
foreach ($pictureElements as $picture)
{
	$pictures[$picture->getAttribute("name")] = array
	(
		"number" => $picture->getAttribute("number"),
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
		// Delete if no longer in XML
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
?>