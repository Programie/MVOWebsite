<?php
switch (Constants::$pagePath[0])
{
	case "files":
		switch (Constants::$pagePath[1])
		{
			case "style.css":
				header("Content-Type: text/css; charset=utf-8");
				$path = ROOT_PATH . "/files/css";
				$dir = scandir($path);
				foreach ($dir as $file)
				{
					if ($file[0] != "." and is_file($path . "/" . $file))
					{
						readfile($path . "/" . $file);
						echo "\n\n";
					}
				}
				exit;
			case "script.js":
				header("Content-Type: text/javascript");
				echo "SCRIPT";
				exit;
		}
		break;
	case "internarea":
		if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
		{
			header("Location: " . BASE_URL . "/internarea");
			exit;
		}
		if (Constants::$pagePath[1])
		{
			switch (Constants::$pagePath[1])
			{
				case "logout":
					Constants::$accountManager->logout();
					header("Location: " . BASE_URL . "/internarea");
					exit;
			}
		}
		else
		{
			if (Constants::$accountManager->getUserId())
			{
				Constants::$pagePath[1] = "home";
			}
			else
			{
				Constants::$pagePath[1] = "login";
			}
		}
		break;
	case "links":
		if (Constants::$pagePath[1])
		{
			$query = Constants::$pdo->prepare("SELECT `url` FROM `links` WHERE `id` = :id");
			$query->execute(array
			(
				":id" => Constants::$pagePath[1]
			));
			$row = $query->fetch();
			$url = $row->url;
			
			$query = Constants::$pdo->prepare("UPDATE `links` SET `clicks` = `clicks` + 1 WHERE `id` = :id");
			$query->execute(array
			(
				":id" => Constants::$pagePath[1]
			));
			
			header("Location: http://" . $url);
			exit;
		}
		break;
}
?>