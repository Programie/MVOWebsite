<h1>Besucherstatistik</h1>

<table class="table">
	<thead>
	<tr>
		<th>Zeit</th>
		<th>G&auml;ste</th>
		<th>Eingeloggte Benutzer</th>
	</tr>
	</thead>
	<tbody>
	<?php
	$stats = array("Heute" => "`date` = CURDATE()", "Gestern" => "`date` = DATE_ADD(CURDATE(), INTERVAL -1 DAY)", "Diese Woche" => "YEARWEEK(`date`, 1) = YEARWEEK(NOW(), 1)", "Letzte Woche" => "YEARWEEK(`date`, 1) = YEARWEEK(CURDATE() - INTERVAL 7 DAY, 1)", "Diesen Monat" => "YEAR(`date`) = YEAR(NOW()) AND MONTH(`date`) = MONTH(NOW())", "Letzten Monat" => "YEAR(`date`) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(`date`) = MONTH(CURDATE() - INTERVAL 1 MONTH)");
	foreach ($stats as $name => $whereSql)
	{
		$guests = 0;
		$users = 0;
		$query = Constants::$pdo->query("SELECT `userId` FROM `visits` WHERE " . $whereSql);
		while ($row = $query->fetch())
		{
			if ($row->userId)
			{
				$users++;
			}
			else
			{
				$guests++;
			}
		}
		echo "
				<tr>
					<td>" . $name . "</td>
					<td>" . $guests . "</td>
					<td>" . $users . "</td>
				</tr>
			";
	}
	?>
	</tbody>
</table>