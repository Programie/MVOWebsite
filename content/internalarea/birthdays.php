<?php
$row = new StdClass;
$row->name = "all";
$row->title = "Alle";
$row->active = true;
$groups = array($row->name => $row);

$activeGroup = "all";

$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups` WHERE `id`");
while ($row = $query->fetch())
{
	if ($row->name == Constants::$pagePath[2])
	{
		$row->active = true;
		$groups["all"]->active = false;
		$activeGroup = $row->name;
	}
	$groups[$row->name] = $row;
}

$users = array();

$now = new DateTime;

$nextBirthDay = null;

$permissionCheckQuery = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = :permission");
$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName`, `birthDate` FROM `users` WHERE `enabled`");
while ($row = $query->fetch())
{
	if ($activeGroup == "all")
	{
		$show = true;
	}
	else
	{
		$permissionCheckQuery->execute(array
		(
			":userId" => $row->id,
			":permission" => "groups." . $activeGroup
		));
		$show = $permissionCheckQuery->rowCount();
	}
	if ($show)
	{
		$birthDate = new DateTime($row->birthDate);
		$row->age = $now->diff($birthDate)->y;
		if (date("m-d", time()) == $birthDate->format("m-d"))
		{
			$addYear = 0;
		}
		else
		{
			$addYear = 1;
		}
		$birthDayInterval = new DateInterval("P" . ($row->age + $addYear) . "Y");
		$birthDate = $birthDate->add($birthDayInterval);
		$row->nextBirthDay = $birthDate->getTimestamp();
		
		if ($nextBirthDay == null or $row->nextBirthDay < $nextBirthDay)
		{
			$nextBirthDay = $row->nextBirthDay;
		}
		
		$users[] = $row;
	}
}

$title = "Geburtstage";

if ($activeGroup != "all")
{
	$title .= " - " . $groups[$activeGroup]->title;
}
echo "<h1>" . $title . "</h1>";
?>

<fieldset id="birthdays_groups" class="no-print">
	<legend>Gruppen</legend>
	<?php
	foreach ($groups as $name => $row)
	{
		$buttonStyle = "";
		if ($row->active)
		{
			$buttonStyle = "style='font-weight: bold;'";
		}
		echo "<a href='/internalarea/birthdays/" . $name . "'><button type='button' " . $buttonStyle . ">" . $row->title . "</button></a>";
	}
	?>
</fieldset>

<table id="birthdays_table" class="table {sortlist: [[2,0]]}">
	<thead>
		<tr>
			<th>Vorname</th>
			<th>Nachname</th>
			<th class="{sorter: 'number-attribute'}">Geburtstag</th>
			<th>Geburtsjahr</th>
			<th>Alter</th>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ($users as $user)
		{
			$rowClasses = array();
			
			if (date("Y-m-d", $user->nextBirthDay) == date("Y-m-d", $nextBirthDay))
			{
				$rowClasses[] = "table_highlight";
			}
			
			$rowAttributes = array();
			
			if (!empty($rowClasses))
			{
				$rowAttributes[] = "class='" . implode(" ", $rowClasses) . "'";
			}
			
			$birthDate = explode("-", $user->birthDate);
			
			echo "
				<tr " . implode(" ", $rowAttributes) . ">
					<td>" . escapeText($user->firstName) . "</td>
					<td>" . escapeText($user->lastName) . "</td>
					<td number='" . $birthDate[1] . $birthDate[2] . "'>" . $birthDate[2] . "." . $birthDate[1] . ".</td>
					<td>" . $birthDate[0] . "</td>
					<td>" . $user->age . "</td>
				</tr>
			";
		}
		?>
	</tbody>
</table>