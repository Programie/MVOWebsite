<?php
class Remote_PictureManager
{
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
		$query->execute(array
		(
			":date" => $params->date,
			":permission" => $params->permission,
			":coverPicture" => $params->coverPicture,
			":name" => $params->name,
			":title" => $params->title,
			":text" => $params->text
		));
		$query = Constants::$pdo->prepare("SELECT `id` FROM `picturealbums` WHERE `date` = :date AND `name` = :name");
		$query->execute(array
		(
			":date" => $params->date,
			":name" => $params->name
		));
		$row = $query->fetch();
		$albumId = $row->id;
		
		if ($params->useAsYearCoverAlbum)
		{
			$query = Constants::$pdo->prepare("REPLACE INTO `pictureyears` (`year`, `coverAlbumId`) VALUES(YEAR(:date), :albumId)");
			$query->execute(array
			(
				":date" => $params->date,
				":albumId" => $albumId
			));
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
				$query->execute(array
				(
					":albumId" => $albumId,
					":number" => $number
					":text" => $pictureData->text
				));
			}
		}
		
		return $albumId ? "ok" : "album_id_is_null";
	}
}
?>