<?php
switch (Constants::$pagePath[1])
{
	case "getalbumdetails":
		if (Constants::$accountManager->hasPermission("pictures.edit"))
		{
			$query = Constants::$pdo->prepare("
				SELECT `published`, `date`, `isPublic`, `title`, `text`, `year`
				FROM `picturealbums`
				LEFT JOIN `pictureyears` ON `pictureyears`.`coverAlbumId` = `picturealbums`.`id`
				WHERE `picturealbums`.`id` = :id
			");
			$query->execute(array
			(
				":id" => Constants::$pagePath[2]
			));
			$row = $query->fetch();
			header("Content-Type: application/json");
			echo json_encode(array
			(
				"published" => (bool) $row->published,
				"title" => $row->title,
				"text" => $row->text,
				"date" => $row->date,
				"isPublic" => (bool) $row->isPublic,
				"albumOfTheYear" => $row->year ? true : false
			));
		}
		exit;
	case "setalbumcover":
		if (Constants::$accountManager->hasPermission("pictures.edit"))
		{
			$queryData = array
			(
				":coverPicture" => $_POST["pictures_setcover_number"],
				":id" => $_POST["pictures_setcover_albumId"]
			);
			$query = Constants::$pdo->prepare("UPDATE `picturealbums` SET `coverPicture` = :coverPicture WHERE `id` = :id");
			if ($query->execute($queryData))
			{
				echo "ok";
			}
			else
			{
				echo "error";
			}
		}
		exit;
	case "setpicturetitle":
		if (Constants::$accountManager->hasPermission("pictures.edit"))
		{
			$queryData = array
			(
				":text" => $_POST["pictures_edittitle_title"],
				":albumId" => $_POST["pictures_edittitle_albumId"],
				":number" => $_POST["pictures_edittitle_number"]
			);
			$query = Constants::$pdo->prepare("UPDATE `pictures` SET `text` = :text WHERE `albumId` = :albumId AND `number` = :number");
			if ($query->execute($queryData))
			{
				echo "ok";
			}
			else
			{
				echo "error";
			}
		}
		exit;
}
?>