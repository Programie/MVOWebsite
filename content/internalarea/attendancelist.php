<?php
$dates = array();
$query = Constants::$pdo->query("SELECT `id`, `startDate`, `groups`, `title` FROM `dates` WHERE `showInAttendanceList` AND `startDate` > NOW() ORDER BY `startDate` ASC");
while ($row = $query->fetch())
{
	$row->groups = explode(",", $row->groups);
	if (!Constants::$accountManager->hasPermissionInArray($row->groups, "dates"))
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
				echo "
					<th class='{sorter: \"number-attribute\"}'>
						<div class='attendancelist_title' title='" . $date->title . "'>" . $date->title . "</div>
						<div class='attendancelist_date'>" . implode(" ", $startDateTime) . "</div>
					</th>
				";
			}
			?>
		</tr>
	</thead>
	<?php
	foreach ($groups as $groupRow)
	{
		echo "
			<tbody class='tablesorter-infoOnly'>
				<tr>
					<th colspan='" . (count($dates) + 1) . "'>" . $groupRow->title . "</th>
				</tr>
			</tbody>
			<tbody>
		";
		foreach ($users as $userRow)
		{
			$checkUserInGroup->execute(array
			(
				":userId" => $userRow->id,
				":permission" => "groups.musiker." . $groupRow->name
			));
			if ($checkUserInGroup->rowCount())
			{
				$attributes = "";
				if ($userRow->id == Constants::$accountManager->getUserId())
				{
					$attributes = "class='table_highlight'";
				}
				echo "<tr userid='" . $userRow->id . "' " . $attributes . ">";
				echo "<td sorttext='" . $userRow->lastName . " " . $userRow->firstName . "'>" . $userRow->firstName . " " . $userRow->lastName . "</td>";
				foreach ($dates as $dateRow)
				{
					$getAttendanceQuery->execute(array
					(
						":dateId" => $dateRow->id,
						":userId" => $userRow->id
					));
					$attendanceRow = $getAttendanceQuery->fetch();
					$name = "attendancelist_" . $dateRow->id . "_" . $userRow->id;
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
						<td dateid='" . $dateRow->id . "' number='" . ($attendanceRow->status == "1" ? "1" : "0") . "' style='white-space: nowrap;'>
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
		}
		echo "</tbody>";
	}
	?>
</table>

<script type="text/javascript">
	function attendancelist_changeState(element)
	{
		var status = null;
		
		var cell = $(element).parents("td");
		var row = cell.parents("tr");
		
		if ($(element).is(":radio"))
		{
			status = $(element).attr("state");
		}
		else
		{
			cell.find("input[type='radio']").each(function()
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
				attendancelist_dateid : cell.attr("dateid"),
				attendancelist_userid : row.attr("userid"),
				attendancelist_status : status
			}
		});
	}
</script>