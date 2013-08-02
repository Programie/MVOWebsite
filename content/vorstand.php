<?php
$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `enabled` ORDER BY `lastname` ASC, `firstName` ASC");
while ($row = $query->fetch())
{
	$users[] = $row;
}
$checkUserInGroup = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = :permission");

$groups = array
(
	array
	(
		"title" => "Vorstand (Gleichberechtigt)",
		"subPermissions" => array("vorstand.1", "vorstand.2")
	),
	array
	(
		"title" => "Kassenverwalterin",
		"subPermissions" => array("kassenverwalter")
	),
	array
	(
		"title" => "Schriftf&uuml;hrerin",
		"subPermissions" => array("schriftfuehrer")
	),
	array
	(
		"title" => "Jugendleiterin",
		"subPermissions" => array("jugendleiter")
	),
	array
	(
		"title" => "Musikervorstand",
		"subPermissions" => array("musikervorstand")
	),
	array
	(
		"title" => "Jugendvertreter",
		"subPermissions" => array("jugendvertreter")
	),
	array
	(
		"title" => "Aktive Beisitzer",
		"subPermissions" => array("aktivebeisitzer")
	),
	array
	(
		"title" => "Passive Beisitzer",
		"subPermissions" => array("passivebeisitzer")
	)
);

echo "
	<h1 class='center'>Der Vorstand</h1>
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
				":permission" => "groups.vorstandschaft." . $subPermission
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