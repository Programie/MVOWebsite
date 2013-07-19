<?php
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

			$errors = 0;
			$ok = 0;

			$sourceData = json_decode(file_get_contents(ROOT_PATH . "/includes/permissions.json"));
			if ($sourceData)
			{
				$groupList = array();
				applypermissions_setGroupIds($sourceData, $groupList);

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
						$queryData = array(":userId" => $row->id, ":permission" => $permission);
						if ($permissionQuery->execute($queryData))
						{
							$ok++;
						} else
						{
							$errors++;
						}
					}
				}
			}

			echo json_encode(array("errors" => $errors, "ok" => $ok));
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
							$permissions[] = array("data" => $permission, "attr" => array("rel" => $type));
						}
						if (!empty($permissions))
						{
							$children[] = array("data" => "Berechtigungen", "attr" => array("rel" => "permission"), "children" => $permissions);
						}
					}

					if ($group->users and !empty($group->users))
					{
						$users = array();
						$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `id` IN(" . implode(",", $group->users) . ") ORDER BY `lastName`, `firstName`");
						while ($row = $query->fetch())
						{
							$users[] = array("data" => $row->firstName . " " . $row->lastName, "attr" => array("rel" => "user"));
						}
						if (!empty($users))
						{
							$children[] = array("data" => "Benutzer", "attr" => array("rel" => "user"), "children" => $users);
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
			$query->execute(array(":id" => Constants::$pagePath[3]));
			$row = $query->fetch();
			$row->id = (int)$row->id;
			$row->enabled = (bool)$row->enabled;
			$data = $row;

			$query = Constants::$pdo->prepare("SELECT `id`, `category`, `subCategory`, `number` FROM `phonenumbers` WHERE `userId` = :userId");
			$query->execute(array(":userId" => $data->id));
			$data->phoneNumbers = $query->fetchAll();

			$profilePictureFile = "/files/profilepictures/" . $data->id . ".jpg";
			if (file_exists(ROOT_PATH . $profilePictureFile))
			{
				$data->profilePictureUrl = "/getprofilepicture/" . $data->id . "/" . md5_file(ROOT_PATH . $profilePictureFile);
			}

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
					$groupData->attr = array("class" => ($group->users and !empty($group->users) and in_array(Constants::$pagePath[3], $group->users)) ? "jstree-checked" : "");
					$groupData->metadata = array("groupId" => $group->id);
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
?>