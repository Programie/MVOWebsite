<?php
/**
 * RPC class for Picture Manager
 *
 * Provides methods to manage pictures.
 */
class Remote_PictureManager
{
	/**
	 * Add a new album
	 *
	 * @param StdClass $params A map containing the following properties:
	 * <ul>
	 *  <li>coverPicture: The number of the picture which should be used as the cover of the album</li>
	 *  <li>date: The date of the album in ISO format (YYYY-MM-DD)</li>
	 *  <li>name: The internal name of the album (Must be unique per year and should not contain special characters like spaces!)</li>
	 *  <li>pictures: A map containing picture specific data for each picture. The property names are the picture numbers starting at 1. Each property must contain a map containing the following properties:
	 *   <ul>
	 *    <li>text: A short text of the picture (Without line breaks!)</li>
	 *   </ul>
	 *  </li>
	 *  <li>permission: The permission required to view the album</li>
	 *  <li>text: The album description</li>
	 *  <li>title: The title of the album</li>
	 *  <li>useAsYearCoverAlbum: True to use the cover picture of this album as the cover picture of the year, false otherwise</li>
	 * </ul>
	 * @return string One of the following values:
	 * <ul>
	 *  <li>album_id_is_null: The album could not be saved (lastInsertId did not return a valid ID)</li>
	 *  <li>ok: The album has been saved successfully</li>
	 * </ul>
	 */
	public function addAlbum($params)
	{
		if (!$params->coverPicture)
		{
			$params->coverPicture = 1;
		}
		if (!$params->text)
		{
			$params->text = "";
		}
		$query = Constants::$pdo->prepare("
			INSERT INTO `picturealbums`
			(`date`, `permission`, `coverPicture`, `name`, `title`, `text`)
			VALUES(:date, :permission, :coverPicture, :name, :title, :text)
			ON DUPLICATE KEY UPDATE
			`permission` = :permission, `coverPicture` = :coverPicture, `title` = :title, `text` = :text
		");
		$query->execute(array(":date" => $params->date, ":permission" => $params->permission, ":coverPicture" => $params->coverPicture, ":name" => $params->name, ":title" => $params->title, ":text" => $params->text));
		$query = Constants::$pdo->prepare("SELECT `id` FROM `picturealbums` WHERE `date` = :date AND `name` = :name");
		$query->execute(array(":date" => $params->date, ":name" => $params->name));
		$row = $query->fetch();
		$albumId = $row->id;

		if ($params->useAsYearCoverAlbum)
		{
			$query = Constants::$pdo->prepare("REPLACE INTO `pictureyears` (`year`, `coverAlbumId`) VALUES(YEAR(:date), :albumId)");
			$query->execute(array(":date" => $params->date, ":albumId" => $albumId));
		}

		$query = Constants::$pdo->prepare("
			INSERT INTO
			`pictures` (`albumId`, `number`, `text`)
			VALUES(:albumId, :number, :text)
			ON DUPLICATE KEY UPDATE
			`text` = :text
		");
		if (is_array($params->pictures))
		{
			foreach ($params->pictures as $number => $pictureData)
			{
				$query->execute(array(":albumId" => $albumId, ":number" => $number, ":text" => $pictureData->text));
			}
		}

		return $albumId ? "ok" : "album_id_is_null";
	}
}

?>