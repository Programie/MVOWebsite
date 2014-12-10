<?php
if (isset($_GET["json"]))
{
	$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups` WHERE `id`");
	$groups = $query->fetchAll();

	$permissionQuery = Constants::$pdo->prepare("
		SELECT `permission`
		FROM `permissions`
		WHERE `userId` = :userId AND `permission` LIKE 'groups.%'
	");

	$phoneNumbersQuery = Constants::$pdo->prepare("
		SELECT `category`, `subCategory`, `number`
		FROM `phonenumbers`
		WHERE `userId` = :userId
	");

	$users = array();

	$query = Constants::$pdo->query("SELECT `id`, `email`, `firstName`, `lastName` FROM `users` WHERE `enabled`");
	while ($row = $query->fetch())
	{
		$row->id = (int) $row->id;

		$row->groups = array();
		$row->phoneNumbers = array();

		$permissionQuery->execute(array
		(
			":userId" => $row->id
		));

		while ($permissionRow = $permissionQuery->fetch())
		{
			list($prefix, $group) = explode(".", $permissionRow->permission, 2);

			$add = false;

			foreach ($groups as $groupRow)
			{
				if ($groupRow->name == $group)
				{
					$add = true;
					break;
				}
			}

			if (!$add)
			{
				continue;
			}

			$row->groups[] = $group;
		}

		if (empty($row->groups))
		{
			continue;
		}

		$phoneNumbersQuery->execute(array
		(
			":userId" => $row->id
		));

		$row->phoneNumbers = $phoneNumbersQuery->fetchAll();

		$users[] = $row;
	}

	header("Content-Type: application/json");
	echo json_encode(array
	(
		"groups" => $groups,
		"users" => $users
	));
	exit;
}