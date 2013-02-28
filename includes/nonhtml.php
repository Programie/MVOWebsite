<?php
switch (Constants::$pagePath[0])
{
	case "internarea":
		if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
		{
			header("Location: " . BASE_URL . "/internarea");
			exit;
		}
		switch (Constants::$pagePath[1])
		{
			case "logout":
				Constants::$accountManager->logout();
				header("Location: " . BASE_URL . "/internarea");
				exit;
		}
		break;
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