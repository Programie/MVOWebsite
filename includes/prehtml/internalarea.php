<?php
if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
{
	if ($_GET["jumpto"])
	{
		header("Location: /" . $_GET["jumpto"]);
	}
	else
	{
		header("Location: /internalarea");
	}
	exit;
}
if (Constants::$pagePath[1])
{
	switch (Constants::$pagePath[1])
	{
		case "attendancelist":
			if (isset($_POST["attendancelist_dateid"]) and isset($_POST["attendancelist_userid"]))
			{
				$status = $_POST["attendancelist_status"];
				$dateId = intval($_POST["attendancelist_dateid"]);
				$userId = intval($_POST["attendancelist_userid"]);
				if ($status != "1" and $status != "0")
				{
					$status = null;
				}
				if ($dateId and $userId)
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `attendancelist` (`dateId`, `userId`, `changeUserId`, `changeTime`, `status`)
						VALUES(:dateId, :userId, :changeUserId, NOW(), :status)
						ON DUPLICATE KEY UPDATE
						`changeUserId` = :changeUserId, `changeTime` = NOW(), `status` = :status
					");
					$query->execute(array
					(
						":dateId" => $_POST["attendancelist_dateid"],
						":userId" => $_POST["attendancelist_userid"],
						":changeUserId" => Constants::$accountManager->getUserId(),
						":status" => $status
					));
					if ($query->rowCount())
					{
						echo "ok";
					}
				}
				exit;
			}
			break;
		case "forms":
			if (Constants::$pagePath[2])
			{
				$query = Constants::$pdo->prepare("SELECT `name` FROM `forms` WHERE `filename` = :filename");
				$query->execute(array
				(
					":filename" => Constants::$pagePath[2]
				));
				$row = $query->fetch();
				if (Constants::$accountManager->hasPermission("forms." . $row->name))
				{
					$filename = ROOT_PATH . "/files/forms/" . Constants::$pagePath[2];
					if (file_exists($filename))
					{
						$file = fopen($filename, "r");
						{
							header("Content-Description: Formular herunterladen");
							header("Content-Type: application/octet-stream");
							header("Content-Disposition: attachment; filename=\"" . Constants::$pagePath[2] . "\"");
							header("Content-Length: " . filesize($filename));
							header("Content-Transfer-Encoding: chunked");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Pragma: public");
							while ($chunk = fread($file, 4096))
							{
								echo $chunk;
							}
							fclose($file);
							exit;
						}
					}
				}
			}
			break;
		case "logout":
			Constants::$accountManager->logout();
			header("Location: " . BASE_URL . "/internalarea");
			exit;
		case "notedirectory":
			if ($_POST["notedirectory_searchstring"])
			{
				break;
			}
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
			break;
		case "roomoccupancyplan":
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
			break;
		case "usermanager":
			if (Constants::$accountManager->hasPermission("usermanager"))
			{
				switch (Constants::$pagePath[2])
				{
					case "getuserdata":
						$query = Constants::$pdo->prepare("SELECT `id`, `enabled`, `username`, `email`, `firstName`, `lastName`, `birthDate` FROM `users` WHERE `id` = :id");
						$query->execute(array
						(
							":id" => Constants::$pagePath[3]
						));
						$row = $query->fetch();
						$row->id = (int) $row->id;
						$row->enabled = (bool) $row->enabled;
						echo json_encode($row);
						exit;
				}
			}
			break;
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
?>