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
$userData = Constants::$accountManager->getUserData();
if ($userData->id and $userData->forcePasswordChange)
{
	$validPages = array("confirmemail", "editprofile", "logout");
	if (!in_array(Constants::$pagePath[1], $validPages))
	{
		header("Location: /internalarea/editprofile#editprofile_changepassword");
		exit;
	}
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
					case "applypermissions":
						function applypermissions_getParentGroups($groupList, $groupId, &$foundGroups)
						{
							if ($groupId)
							{
								$group = $groupList[$groupId];
								if ($group->parentGroupId)
								{
									$foundGroups[] = $group->parentGroupId;
									applypermissions_getParentGroups($groupList, $group->parentGroupId, $foundGroups);
								}
							}
						}
						function applypermissions_searchUserInGroups($groupList, $groups, $userId, &$foundGroups)
						{
							foreach ($groups as $group)
							{
								if ($group->users)
								{
									if (in_array($userId, $group->users) and !in_array($group->id, $foundGroups))
									{
										$foundGroups[] = $group->id;
										applypermissions_getParentGroups($groupList, $group->id, $foundGroups);
									}
								}
								if ($group->subGroups)
								{
									applypermissions_searchUserInGroups($groupList, $group->subGroups, $userId, $foundGroups);
								}
							}
						}
						function applypermissions_setGroupIds(&$groups, &$groupList, $id = 0)
						{
							$parentGroupId = $id;
							foreach ($groups as $group)
							{
								$id++;
								$groupId = $id;
								$group->parentGroupId = $parentGroupId;
								$group->id = $groupId;
								$groupList[$groupId] = clone $group;
								if ($group->subGroups)
								{
									$id = applypermissions_setGroupIds($group->subGroups, $groupList, $id);
									$childIds = array();
									foreach ($group->subGroups as $subGroup)
									{
										$childIds[] = $subGroup->id;
									}
									$groupList[$groupId]->subGroups = $childIds;
								}
							}
							return $id;
						}
						
						$sourceData = json_decode(file_get_contents(ROOT_PATH . "/includes/permissions.json"));
						$groupList = array();
						applypermissions_setGroupIds($sourceData, $groupList);
						
						$errors = 0;
						$ok = 0;
						Constants::$pdo->query("TRUNCATE TABLE `permissions`");
						$query = Constants::$pdo->query("SELECT `id` FROM `users`");
						$permissionQuery = Constants::$pdo->prepare("INSERT INTO `permissions` (`userId`, `permission`) VALUES(:userId, :permission)");
						while ($row = $query->fetch())
						{
							$users++;
							$foundGroups = array();
							$permissions = array();
							applypermissions_searchUserInGroups($groupList, $sourceData, $row->id, $foundGroups);
							foreach ($foundGroups as $groupId)
							{
								$group = $groupList[$groupId];
								if ($group->permissions)
								{
									foreach ($group->permissions as $permission)
									{
										if (!in_array($permission, $permissions))
										{
											$permissions[] = $permission;
										}
									}
								}
							}
							foreach ($permissions as $permission)
							{
								$queryData = array
								(
									":userId" => $row->id,
									":permission" => $permission
								);
								if ($permissionQuery->execute($queryData))
								{
									$ok++;
								}
								else
								{
									$errors++;
								}
							}
						}
						
						echo json_encode(array
						(
							"errors" => $errors,
							"ok" => $ok
						));
						exit;
					case "getpermissiongroups":
						$sourceData = json_decode(file_get_contents(ROOT_PATH . "/includes/permissions.json"));
						function getpermissiongroups_createTree($sourceData)
						{
							$data = array();
							foreach ($sourceData as $group)
							{
								$groupData = new StdClass;
								$groupData->data = $group->title;
								$children = array();
								if ($group->subGroups and !empty($group->subGroups))
								{
									$children = getpermissiongroups_createTree($group->subGroups);
								}
								if ($group->permissions and !empty($group->permissions))
								{
									$permissions = array();
									foreach ($group->permissions as $permission)
									{
										$type = "permission";
										if (substr($permission, 0, 1) == "-")
										{
											$type = "permission_revoked";
											$permission = substr($permission, 1);
										}
										$permissions[] = array
										(
											"data" => $permission,
											"attr" => array
											(
												"rel" => $type
											)
										);
									}
									if (!empty($permissions))
									{
										$children[] = array
										(
											"data" => "Berechtigungen",
											"attr" => array
											(
												"rel" => "permission"
											),
											"children" => $permissions
										);
									}
								}
								if ($group->users and !empty($group->users))
								{
									$users = array();
									$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `id` IN(" . implode(",", $group->users) . ") ORDER BY `lastName`, `firstName`");
									while ($row = $query->fetch())
									{
										$users[] = array
										(
											"data" => $row->firstName . " " . $row->lastName,
											"attr" => array
											(
												"rel" => "user"
											)
										);
									}
									if (!empty($users))
									{
										$children[] = array
										(
											"data" => "Benutzer",
											"attr" => array
											(
												"rel" => "user"
											),
											"children" => $users
										);
									}
								}
								if ($children)
								{
									$groupData->children = $children;
								}
								$data[] = $groupData;
							}
							return $data;
						}
						echo json_encode(getpermissiongroups_createTree($sourceData));
						exit;
					case "getuserdata":
						$query = Constants::$pdo->prepare("SELECT `id`, `enabled`, `username`, `email`, `firstName`, `lastName`, `birthDate` FROM `users` WHERE `id` = :id");
						$query->execute(array
						(
							":id" => Constants::$pagePath[3]
						));
						$row = $query->fetch();
						$row->id = (int) $row->id;
						$row->enabled = (bool) $row->enabled;
						$data = $row;
						$query = Constants::$pdo->prepare("SELECT `id`, `category`, `subCategory`, `number` FROM `phonenumbers` WHERE `userId` = :userId");
						$query->execute(array
						(
							":userId" => $data->id
						));
						$data->phoneNumbers = $query->fetchAll();
						echo json_encode($data);
						exit;
					case "getuserpermissions":
						$sourceData = json_decode(file_get_contents(ROOT_PATH . "/includes/permissions.json"));
						function getuserpermissions_createTree($sourceData)
						{
							$data = array();
							foreach ($sourceData as $group)
							{
								$groupData = new StdClass;
								$groupData->data = $group->title;
								$groupData->attr = array
								(
									"class" => ($group->users and !empty($group->users) and in_array(Constants::$pagePath[3], $group->users)) ? "jstree-checked" : ""
								);
								$groupData->metadata = array
								(
									"groupId" => $group->id
								);
								$children = array();
								if ($group->subGroups and !empty($group->subGroups))
								{
									$groupData->children = getuserpermissions_createTree($group->subGroups);
								}
								$data[] = $groupData;
							}
							return $data;
						}
						echo json_encode(getuserpermissions_createTree($sourceData));
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