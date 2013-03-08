<?php
switch (Constants::$pagePath[0])
{
	case "dates":
		$calendar = Constants::$pagePath[1];
		if ($calendar == "internal.ics" or $calendar == "public.ics")
		{
			if ($calendar == "internal.ics")
			{
				if (!isset($_SERVER["PHP_AUTH_USER"]) or !isset($_SERVER["PHP_AUTH_PW"]))
				{
					header("WWW-Authenticate: Basic realm='My Realm'");
					header("HTTP/1.0 401 Unauthorized");
					exit;
				}
			}
			
			header("Content-Type: text/calendar");
			
			require_once ROOT_PATH . "/includes/icalendar/iCalWriter.php";
			
			$calendar = new iCalWriter;
			$calendar->setDownloadOutput();
			$calendar->start();
			
			$event = new iCalEvent;
			
			$dates = Dates::getDates();
			foreach ($dates as $date)
			{
				if (date("H:i:s", $date->startDate) == "00:00:00")
				{
					$useStartTime = false;
				}
				else
				{
					$useStartTime = true;
				}
				$event->setStart(date("Y", $date->startDate), date("m", $date->startDate), date("d", $date->startDate), false, true, "", $useStartTime, date("H", $date->startDate), date("i", $date->startDate), date("s", $date->startDate));
				
				if (date("Y-m-d", $date-$date->endDate) != "1970-01-01")
				{
					if (date("H:i:s", $date->endDate) == "00:00:00")
					{
						$date->endDate += 60 * 60 * 24;// End date specifies the non-inclusive end of the event
						$useEndTime = false;
					}
					else
					{
						$useEndTime = true;
					}
					$event->setEnd(date("Y", $date->endDate), date("m", $date->endDate), date("d", $date->endDate), false, true, "", $useEndTime, date("H", $date->endDate), date("i", $date->endDate), date("s", $date->endDate));
				}
				$event->setShortDescription($date->title);
				$event->setLocation($date->locationName);
				$event->setUID($row->id . "-" . $date->startDate . "@public.dates." . $_SERVER["SERVER_NAME"]);
				$calendar->add($event);
				$event->clear();
			}
			
			$calendar->end();
			exit;
		}
		break;
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