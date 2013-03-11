<?php
$dates = array();
$query = Constants::$pdo->query("SELECT `id`, `startDate`, `permission`, `title` FROM `dates` WHERE `showInAttendanceList` AND `startDate` > NOW() ORDER BY `startDate` ASC");
while ($row = $query->fetch())
{
	if (!Constants::$accountManager->hasPermission($row->permission))
	{
		continue;
	}
	$dates[] = $row;
	if (count($dates) >= 5)
	{
		break;
	}
}

$groups = array();
$users = array();
$query = Constants::$pdo->query("SELECT `musiciangroups`.`name` AS `groupName`, `musiciangroups`.`title` AS `groupTitle`, `users`.`firstName`, `users`.`lastName`, `users`.`id` AS `id` FROM `users` LEFT JOIN `musiciangroups` ON `musiciangroups`.`id` = `users`.`musicianGroupId` WHERE `users`.`musicianGroupId` ORDER BY `musiciangroups`.`orderIndex` ASC, `musiciangroups`.`title` ASC, `users`.`lastname` ASC, `users`.`firstName` ASC");
while ($row = $query->fetch())
{
	$groups[$row->groupName] = $row->groupTitle;
	$users[$row->groupName][] = $row;
}

$getAttendanceQuery = Constants::$pdo->prepare("SELECT `status` FROM `attendancelist` WHERE `dateId` = :dateId AND `userId` = :userId");
?>

<h1>Anwesenheitsliste</h1>

<table class="table {sortlist: [[0,0]]}">
	<thead>
		<tr>
			<th class="{sorter: 'text-attribute'}">Name</th>
			<?php
			foreach ($dates as $date)
			{
				$startDateTime = strtotime($date->startDate);
				$startDate = date("d.m.Y", $startDateTime);
				$startTime = date("H:i", $startDateTime);
				$startDateTime = array(getWeekdayName(date("N", $startDateTime), false), $startDate);
				if ($startTime != "00:00")
				{
					$startDateTime[] = $startTime . " Uhr";
				}
				echo "<th class='{sorter: \"number-attribute\"}'>" . $date->title . "<br /><div class='attendancelist_date'>" . implode(" ", $startDateTime) . "</div></th>";
			}
			?>
		</tr>
	</thead>
	<?php
	foreach ($groups as $groupName => $groupTitle)
	{
		echo "
			<tbody class='tablesorter-infoOnly'>
				<tr>
					<th colspan='" . (count($dates) + 1) . "'>" . $groupTitle . "</th>
				</tr>
			</tbody>
			<tbody>
		";
		foreach ($users[$groupName] as $user)
		{
			$attributes = "";
			if ($user->id == Constants::$accountManager->getUserId())
			{
				$attributes = "class='table_highlight'";
			}
			echo "<tr userid='" . $user->id . "' " . $attributes . ">";
			echo "<td sorttext='" . $user->lastName . " " . $user->firstName . "'>" . $user->firstName . " " . $user->lastName . "</td>";
			foreach ($dates as $date)
			{
				$getAttendanceQuery->execute(array
				(
					":dateId" => $date->id,
					":userId" => $user->id
				));
				$attendanceRow = $getAttendanceQuery->fetch();
				$name = "attendancelist_" . $date->id . "_" . $user->id;
				$statusText = "";
				switch ($attendanceRow->status)
				{
					case "1":
						$statusText = "Ja";
						break;
					case "0":
						$statusText = "Nein";
						break;
				}
				echo "
					<td dateid='" . $date->id . "' number='" . ($attendanceRow->status == "1" ? "1" : "0") . "' style='white-space: nowrap;'>
						<div class='no-print'>
							<input type='radio' state='1' name='" . $name . "' id='" . $name . "_yes' onclick='attendancelist_changeState(this);' " . ($attendanceRow->status == "1" ? "checked='checked'" : "") . "/><label for='" . $name . "_yes'>Ja</label>
							<input type='radio' state='0' name='" . $name . "' id='" . $name . "_no' onclick='attendancelist_changeState(this);' " . ($attendanceRow->status == "0" ? "checked='checked'" : "") . "/><label for='" . $name . "_no'>Nein</label>
							<button type='button' title='Auswahl entfernen' onclick='attendancelist_changeState(this);'><img src='/files/images/formfields/trash.png' alt='X'/></button>
						</div>
						<div class='print-only'>" . $statusText . "</div>
					</td>
				";
			}
			echo "</tr>";
		}
		echo "</tbody>";
	}
	?>
</table>

<script type="text/javascript">
	function attendancelist_changeState(element)
	{
		var status = null;
		
		var cell = element.parentNode;
		var row = cell.parentNode;
		
		if (element.getAttribute("type") == "radio")
		{
			status = element.getAttribute("state");
		}
		else
		{
			$(cell).find("input[type='radio']").each(function()
			{
				$(this).prop("checked", false);
			});
		}
		$.ajax(
		{
			type : "POST",
			url : "/internalarea/attendancelist",
			data :
			{
				attendancelist_dateid : cell.getAttribute("dateid"),
				attendancelist_userid : row.getAttribute("userid"),
				attendancelist_status : status
			}
		});
	}
</script>