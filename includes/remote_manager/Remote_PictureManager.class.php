<?php
class Remote_PictureManager
{
	public function addAlbum($params)
	{
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
		
		$query = Constants::$pdo->prepare("
			INSERT INTO
			`pictures` (`albumId`, `number`, `text`)
			VALUES(:albumId, :number, :text)
			ON DUPLICATE KEY UPDATE
			`text` = :text
		");
		foreach ($params->pictures as $pictureData)
		{
			$query->execute(array
			(
				":albumId" => $albumId,
				":number" => $pictureData->id,
				":text" => $pictureData->text
			));
		}
		
		return $albumId ? "ok" : "album_id_is_null";
	}
}
?>