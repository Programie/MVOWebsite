<?php
if (Constants::$pagePath[1])
{
	$query = Constants::$pdo->prepare("SELECT `url` FROM `links` WHERE `id` = :id");
	$query->execute(array(":id" => Constants::$pagePath[1]));
	$row = $query->fetch();
	$url = $row->url;

	if ($url)
	{
		$query = Constants::$pdo->prepare("UPDATE `links` SET `clicks` = `clicks` + 1 WHERE `id` = :id");
		$query->execute(array(":id" => Constants::$pagePath[1]));

		header("Location: http://" . $url);
		exit;
	}
}
?>