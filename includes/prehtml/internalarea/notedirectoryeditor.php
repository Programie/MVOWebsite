<?php
if (Constants::$accountManager->hasPermission("notedirectory.edit"))
{
	switch (Constants::$pagePath[2])
	{
		case "getprogramtitles":
			$query = Constants::$pdo->prepare("
				SELECT
					`notedirectory_programtitles`.`id`,
					`notedirectory_programtitles`.`number`,
					`notedirectory_titles`.`title`
				FROM `notedirectory_programtitles`
				LEFT JOIN `notedirectory_titles` ON `notedirectory_titles`.`id` = `notedirectory_programtitles`.`titleId`
				WHERE `notedirectory_programtitles`.`programId` = :programId
				ORDER BY `number` ASC
			");
			$query->execute(array
			(
				":programId" => Constants::$pagePath[3]
			));
			echo json_encode($query->fetchAll());
			exit;
		case "gettitles":
			$query = Constants::$pdo->query("SELECT `id`, `title` FROM `notedirectory_titles`");
			echo json_encode($query->fetchAll());
			exit;
	}
}
?>