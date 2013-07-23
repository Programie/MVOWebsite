<h1>Verbesserungsvorschl&auml;ge</h1>

<?php
if (isset($_POST["featurerequests_new_description"]))
{
	if ($_POST["featurerequests_new_sendtoken"] == TokenManager::getSendToken("featurerequests_new"))
	{
		if ($_POST["featurerequests_new_description"])
		{
			$query = Constants::$pdo->prepare("INSERT INTO `featurerequests` (`userId`, `date`, `description`, `status`) VALUES(:userId, NOW(), :description, 'new')");
			$query->execute(array(":userId" => Constants::$accountManager->getUserId(), ":description" => $_POST["featurerequests_new_description"]));
			echo "<p class='alert-success'>Dein Verbesserungsvorschlag wurde erfolgreich eingetragen.</p>";
		}
		else
		{
			echo "<p class='alert-error'>Keine Beschreibung angegeben!</p>";
		}
	}
	else
	{
		echo "<p class='alert-error'>Es wurde versucht, das Formular erneut zu senden!</p>";
	}
}
?>

<fieldset id="featurerequests_new_fieldset">
	<legend>Neuer Verbesserungsvorschlag</legend>

	<form action="/internalarea/featurerequests" method="post">
		<textarea id="featurerequests_new_description" name="featurerequests_new_description" rows="5"
			  cols="15"></textarea>

		<input type="hidden" name="featurerequests_new_sendtoken"
		       value="<?php echo TokenManager::getSendToken("featurerequests_new", true); ?>"/>

		<input type="submit" value="OK"/>
	</form>
</fieldset>

<?php
$query = Constants::$pdo->query("SELECT `users`.`firstName`, `users`.`lastName`, `featurerequests`.`date`, `featurerequests`.`description`, `featurerequests`.`status` FROM `featurerequests` LEFT JOIN `users` ON `users`.`id` = `featurerequests`.`userId`");
if ($query->rowCount())
{
	echo "
		<table class='table {sortlist: [[2,1]]}'>
			<thead>
				<tr>
					<th>Ersteller</th>
					<th>Beschreibung</th>
					<th class=\"{sorter: 'number-attribute'}\">Datum</th>
					<th class=\"{sorter: 'number-attribute'}\">Status</th>
				</tr>
			</thead>
			<tbody>
	";
	$statusTypes = array("new" => array("order" => 1, "title" => "Neu"), "accepted" => array("order" => 2, "title" => "Angenommen"), "declined" => array("order" => 2, "title" => "Abgelehnt"), "wip" => array("order" => 3, "title" => "In Bearbeitung"), "waitingforrelease" => array("order" => 4, "title" => "Ab dem n&auml;chsten Onlinegang verf&uuml;gbar"), "done" => array("order" => 5, "title" => "Fertig"));
	while ($row = $query->fetch())
	{
		$row->date = strtotime($row->date);
		echo "
			<tr>
				<td>" . escapeText($row->firstName . " " . $row->lastName) . "</td>
				<td>" . formatText($row->description) . "</td>
				<td number='" . $row->date . "'>" . date("d.m.Y", $row->date) . "</td>
				<td number='" . $statusTypes[$row->status]["order"] . "'>" . $statusTypes[$row->status]["title"] . "</td>
			</tr>
		";
	}
	echo "
			</tbody>
		</table>
	";
}
else
{
	echo "<p class='alert-error'>Keine Verbesserungsvorschl&auml;ge vorhanden!</p>";
}
?>