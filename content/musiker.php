<?php
$groups = array();
$users = array();

$query = Constants::$pdo->query("SELECT `musiciangroups`.`name` AS `groupName`, `musiciangroups`.`title` AS `groupTitle`, `users`.`firstName`, `users`.`lastName`, `users`.`id` AS `userId` FROM `users` LEFT JOIN `musiciangroups` ON `musiciangroups`.`id` = `users`.`musicianGroupId` WHERE `users`.`musicianGroupId` ORDER BY `musiciangroups`.`orderIndex` ASC, `musiciangroups`.`title` ASC, `users`.`lastname` ASC, `users`.`firstName` ASC");
while ($row = $query->fetch())
{
	$groups[$row->groupName] = $row->groupTitle;
	$users[$row->groupName][] = $row;
}

echo "<h1>Musiker</h1>";

foreach ($groups as $groupName => $groupTitle)
{
	echo "<h2>" . $groupTitle . "</h2>";
	
	echo "<ul class='polaroids'>";
	foreach ($users[$groupName] as $user)
	{
		$file = "/files/profilepictures/" . $user->userId . ".jpg";
		echo "
			<li>
				<a href='/files/profilepictures/" . $user->userId . ".jpg' rel='colorbox' caption='" . $user->firstName . " " . $user->lastName . "'>
					<img class='profilepicture' src='" . $file . "?md5=" . @md5_file(ROOT_PATH . $file) . "'/>
				</a>
			</li>
		";
	}
	echo "</ul>";
	
	echo "<div class='clear'></div>";
}
?>