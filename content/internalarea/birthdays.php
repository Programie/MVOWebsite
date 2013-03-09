<h1>Geburtstage</h1>

<?php
$users = array();

$now = new DateTime;

$nextBirthDay = null;

$query = Constants::$pdo->query("SELECT `firstName`, `lastName`, `birthDate` FROM `users`");
while ($row = $query->fetch())
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
?>

<table id="birthdays_table" class="table">
	<thead>
		<tr>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Geburtstag</th>
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
					<td>" . $user->firstName . "</td>
					<td>" . $user->lastName . "</td>
					<td number='" . $birthDate[1] . $birthDate[2] . "'>" . $birthDate[2] . "." . $birthDate[1] . ".</td>
					<td>" . $birthDate[0] . "</td>
					<td>" . $user->age . "</td>
				</tr>
			";
		}
		?>
	</tbody>
</table>

<script type="text/javascript">
	$("#birthdays_table").tablesorter(
	{
		headers :
		{
			2 :
			{
				sorter : "number-attribute"
			}
		},
		sortList : [[2, 0]]
	});
</script>