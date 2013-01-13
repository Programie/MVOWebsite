<?php
switch (Constants::$pagePath[0])
{
	case "links":
		$url = Constants::$pagePath[1];
		if ($url)
		{
			$query = Constants::$pdo->prepare("UPDATE `links` SET `clicks` = `clicks` + 1 WHERE `url` = :url");
			$query->execute(array
			(
				":url" => $url
			));
			header("Location: http://" . $url);
			exit;
		}
		break;
}
?>