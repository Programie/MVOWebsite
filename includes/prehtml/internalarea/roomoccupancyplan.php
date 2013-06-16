<?php
switch (Constants::$pagePath[2])
{
	case "editevent":
		if (Constants::$accountManager->hasPermission("roomoccupancyplan.edit"))
		{
			$date = explode(".", $_POST["roomoccupancyplan_date"]);
			$endRepeat = explode(".", $_POST["roomoccupancyplan_endrepeat"]);
			if (@checkdate($date[1], $date[0], $date[2]) and (!$_POST["roomoccupancyplan_weekly"] or !$_POST["roomoccupancyplan_endrepeat"] or @checkdate($endRepeat[1], $endRepeat[0], $endRepeat[2])))
			{
				if ($_POST["roomoccupancyplan_weekly"] and $_POST["roomoccupancyplan_endrepeat"])
				{
					$endRepeat = $endRepeat[2] . "-" . $endRepeat[1] . "-" . $endRepeat[0];
				}
				else
				{
					$endRepeat = null;
				}
				$queryData = array
				(
					":title" => $_POST["roomoccupancyplan_title"],
					":reservedBy" => $_POST["roomoccupancyplan_reservedby"],
					":date" => $date[2] . "-" . $date[1] . "-" . $date[0],
					":startTime" => $_POST["roomoccupancyplan_starttime"],
					":endTime" => $_POST["roomoccupancyplan_endtime"],
					":weekly" => $_POST["roomoccupancyplan_weekly"],
					":endRepeat" => $endRepeat,
					":changeUserId" => Constants::$accountManager->getUserId()
				);
				if ($_POST["roomoccupancyplan_eventid"])
				{
					$query = Constants::$pdo->prepare("
						UPDATE `roomoccupancyplan`
						SET
							`title` = :title,
							`reservedBy` = :reservedBy,
							`date` = :date,
							`startTime` = :startTime,
							`endTime` = :endTime,
							`endRepeat` = :endRepeat,
							`weekly` = :weekly,
							`changeUserId` = :changeUserId,
						WHERE `id` = :id
					");
					$queryData[":id"] = $_POST["roomoccupancyplan_eventid"];
				}
				else
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `roomoccupancyplan` (`title`, `reservedBy`, `date`, `startTime`, `endTime`, `endRepeat`, `weekly`, `changeUserId`)
						VALUES(:title, :reservedBy, :date, :startTime, :endTime, :endRepeat, :weekly, :changeUserId)
					");
				}
				if ($query->execute($queryData))
				{
					echo "ok";
				}
			}
			else
			{
				echo "invalid date";
			}
		}
		exit;
	case "getevents":
		if (Constants::$accountManager->hasPermission("roomoccupancyplan.view"))
		{
			$query = Constants::$pdo->query("
				SELECT
					`id`,
					`title`,
					`reservedBy`,
					`date`,
					`startTime`,
					`endTime`,
					`endRepeat`,
					`weekly`
				FROM `roomoccupancyplan`
			");
			
			$data = array();
			while ($row = $query->fetch())
			{
				if ($row->weekly)
				{
					$weekday = date("l", strtotime($row->date));
					$dateTimestamp = $_GET["start"];
					$firstEvent = true;
					while (true)
					{
						if ($firstEvent)// The first event have to be "this X" (e.g. This Monday) because today can be Monday and with "next" it would be the next week
						{
							$dateTimestamp = strtotime("this " . $weekday, $dateTimestamp);
							$firstEvent = false;
						}
						else
						{
							$dateTimestamp = strtotime("next " . $weekday, $dateTimestamp);
						}
						if ($dateTimestamp < strtotime($row->date))// This event would be before the defined start date
						{
							continue;
						}
						if ($row->endRepeat and $dateTimestamp > strtotime($row->endRepeat))// This event would be after the defined repeating end (endRepeat = NULL means repeats forever)
						{
							break;
						}
						if ($dateTimestamp < $_GET["start"] or $dateTimestamp > $_GET["end"])// This event is out of the visible range
						{
							break;
						}
						$data[] = array
						(
							"id" => $row->id,
							"title" => $row->title,
							"reservedBy" => $row->reservedBy,
							"start" => strtotime(date("Y-m-d", $dateTimestamp) . " " . $row->startTime),
							"end" => strtotime(date("Y-m-d", $dateTimestamp) . " " . $row->endTime),
							"endRepeat" => strtotime($row->endRepeat),
							"weekly" => true
						);
					}
				}
				else
				{
					$data[] = array
					(
						"id" => $row->id,
						"title" => $row->title,
						"reservedBy" => $row->reservedBy,
						"start" => $row->date . "T" . $row->startTime . "Z",
						"end" => $row->date . "T" . $row->endTime . "Z"
					);
				}
			}
			echo json_encode($data);
		}
		exit;
	case "moveevent":
		if (Constants::$accountManager->hasPermission("roomoccupancyplan.edit"))
		{
			$query = Constants::$pdo->prepare("
				SELECT `id`, `date`, `startTime`, `endTime`
				FROM `roomoccupancyplan`
				WHERE `id` = :id
			");
			$query->execute(array
			(
				":id" => $_POST["roomoccupancyplan_eventid"]
			));
			$row = $query->fetch();
			if ($row->id)
			{
				$query = Constants::$pdo->prepare("
					UPDATE `roomoccupancyplan`
					SET
						`date` = :date,
						`startTime` = :startTime,
						`endTime` = :endTime,
						`changeUserId` = :changeUserId
					WHERE `id` = :id
				");
				$queryData = array
				(
					":date" => date("Y-m-d", strtotime("+" . intval($_POST["roomoccupancyplan_daydelta"]) . " day", strtotime($row->date))),
					":startTime" => date("H:i:s", strtotime($row->startTime) + 60 * $_POST["roomoccupancyplan_minutedelta"]),
					":endTime" => date("H:i:s", strtotime($row->endTime) + 60 * $_POST["roomoccupancyplan_minutedelta"]),
					":changeUserId" => Constants::$accountManager->getUserId(),
					":id" => $_POST["roomoccupancyplan_eventid"]
				);
				if ($query->execute($queryData))
				{
					echo "ok";
				}
			}
		}
		exit;
	case "resizeevent":
		if (Constants::$accountManager->hasPermission("roomoccupancyplan.edit"))
		{
			$query = Constants::$pdo->prepare("
				SELECT `id`, `endTime`
				FROM `roomoccupancyplan`
				WHERE `id` = :id
			");
			$query->execute(array
			(
				":id" => $_POST["roomoccupancyplan_eventid"]
			));
			$row = $query->fetch();
			if ($row->id)
			{
				$query = Constants::$pdo->prepare("
					UPDATE `roomoccupancyplan`
					SET
						`endTime` = :endTime,
						`changeUserId` = :changeUserId
					WHERE `id` = :id
				");
				$queryData = array
				(
					":endTime" => date("H:i:s", strtotime($row->endTime) + 60 * $_POST["roomoccupancyplan_minutedelta"]),
					":changeUserId" => Constants::$accountManager->getUserId(),
					":id" => $_POST["roomoccupancyplan_eventid"]
				);
				if ($query->execute($queryData))
				{
					echo "ok";
				}
			}
		}
		exit;
}
?>