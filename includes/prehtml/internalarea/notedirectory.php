<?php
if (!$_POST["notedirectory_searchstring"])
{
	if (!Constants::$pagePath[2])
	{
		Constants::$pagePath[2] = "program";
	}
	if (Constants::$pagePath[2] == "program" and !Constants::$pagePath[3])
	{
		$query = Constants::$pdo->query("SELECT `notedirectory_programs`.`id` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId` WHERE `notedirectory_programs`.`year` = YEAR(NOW()) AND `notedirectory_programtypes`.`showNoSelection`");
		if ($query->rowCount())
		{
			$row = $query->fetch();
			Constants::$pagePath[3] = $row->id;
		}
		if (Constants::$pagePath[3])
		{
			header("Location: /" . implode("/", Constants::$pagePath));
			exit;
		}
	}
}
?>