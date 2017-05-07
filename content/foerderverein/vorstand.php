<?php
$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `enabled` ORDER BY `lastName` ASC, `firstName` ASC");
while ($row = $query->fetch())
{
	$users[] = $row;
}
$checkUserInGroup = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = :permission");

$groups = array
(
	array
	(
		"title" => "Vorstandsteam",
		"subPermissions" => array("vorstand")
	),
	array
	(
		"title" => "Kassenverwalter",
		"subPermissions" => array("kassenverwalter")
	),
	array
	(
		"title" => "Schriftf&uuml;hrer",
		"subPermissions" => array("schriftfuehrer")
	)
);

echo "
	<h1 class='center'>Der Vorstand des F&ouml;rdervereins</h1>
	<table align='center' cellspacing='10'>
		<tbody>
";

foreach ($groups as $group)
{
	$userList = array();

	foreach ($group["subPermissions"] as $subPermission)
	{
		foreach ($users as $user)
		{
			$checkUserInGroup->execute(array
			(
				":userId" => $user->id,
				":permission" => "groups.foerderverein.vorstandschaft." . $subPermission
			));
			if ($checkUserInGroup->rowCount())
			{
				$userList[] = $user->firstName . " " . $user->lastName;
			}
		}
	}

	if (!empty($userList))
	{
		echo "
			<tr>
				<td>" . $group["title"] . "</td>
				<td>" . implode(", ", $userList) . "</td>
			</tr>
		";
	}
}

echo "
		</tbody>
	</table>
";
?>