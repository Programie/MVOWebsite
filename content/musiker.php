<?php
$groups = array();
$users = array();

$query = Constants::$pdo->query("SELECT `name`, `title` FROM `musiciangroups` ORDER BY `orderIndex` ASC, `title` ASC");
while ($row = $query->fetch())
{
	$groups[] = $row;
}

$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName` FROM `users` ORDER BY `lastname` ASC, `firstName` ASC");
while ($row = $query->fetch())
{
	$users[] = $row;
}

$checkUserInGroup = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = :permission");
$checkHide = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = '-show.public'");

echo "<h1>Musiker</h1>";

foreach ($groups as $groupRow)
{
	echo "<h2>" . $groupRow->title . "</h2>";
	
	echo "<ul class='musiker polaroids'>";
	foreach ($users as $userRow)
	{
		$checkUserInGroup->execute(array
		(
			":userId" => $userRow->id,
			":permission" => "groups.musiker." . $groupRow->name
		));
		if ($checkUserInGroup->rowCount())
		{
			$checkHide->execute(array
			(
				":userId" => $userRow->id
			));
			if (!$checkHide->rowCount())
			{
				$file = "/files/profilepictures/" . $userRow->id . ".jpg";
				$url = $file . "?md5=" . @md5_file(ROOT_PATH . $file);
				echo "
					<li>
						<a href='" . $url . "' caption='" . $userRow->firstName . " " . $userRow->lastName . "'>
							<img class='profilepicture' src='" . $url . "' alt='" . $userRow->firstName . " " . $userRow->lastName . "'/>
						</a>
					</li>
				";
			}
		}
	}
	echo "
		</ul>
		
		<div class='clear'></div>
	";
}
?>

<script type='text/javascript'>
	$(".musiker").photobox("li > a",
	{
		history : false,
		time : 10000
	});
</script>