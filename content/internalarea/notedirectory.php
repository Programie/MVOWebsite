<h1>Notenverzeichnis</h1>

<ul id="notedirectory_selectionmenu" class="menu no-print">
	<li>
		<a href="#">Auswahl</a>
		<ul>
			<?php
			$years = array();
			$query = Constants::$pdo->query("SELECT `notedirectory_programs`.`id`, `year`, `title` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`");
			while ($row = $query->fetch())
			{
				$years[$row->year][$row->id] = $row->title;
			}
			foreach ($years as $year => $programs)
			{
				echo "
					<li>
						<a href='#'>" . $year . "</a>
						<ul>
				";
				foreach ($programs as $id => $title)
				{
					echo "<li><a href='/internalarea/notedirectory/" . $id . "'>" . $title . "</a></li>";
				}
				echo "
						</ul>
					</li>
				";
			}
			?>
			<li><a href="/internalarea/notedirectory/all">Alle</a></li>
		</ul>
	</li>
</ul>

<div id="notedirectory_searchform_div1">
	<form id="notedirectory_searchform" action="/internalarea/notedirectory" method="post">
		<input type="text" class="input-search" id="notedirectory_searchstring" name="notedirectory_searchstring" placeholder="Suchbegriff" value="<?php echo htmlspecialchars($_POST["notedirectory_searchstring"]);?>"/>
	</form>
</div>

<?php
if ($_POST["notedirectory_searchstring"])
{
	$titleQuery = Constants::$pdo->prepare("
		SELECT `notedirectory_titles`.`id`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`
		FROM `notedirectory_titles`
		LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`
		WHERE
			`notedirectory_titles`.`title` LIKE :searchstring OR
			`notedirectory_titles`.`composer` LIKE :searchstring OR
			`notedirectory_titles`.`arranger` LIKE :searchstring OR
			`notedirectory_categories`.`title` LIKE :searchstring
	");
	$titleQuery->execute(array
	(
		":searchstring" => "%" . $_POST["notedirectory_searchstring"] . "%"
	));
	
	$programQuery = Constants::$pdo->prepare("
		SELECT `year`, `title`
		FROM `notedirectory_programtitles`
		LEFT JOIN `notedirectory_programs` ON `notedirectory_programs`.`id` =`notedirectory_programtitles`.`programId`
		LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`
		WHERE `notedirectory_programtitles`.`titleId` = :titleId
	");
	
	$categories = array();
	$programs = array();
	
	while ($titleRow = $titleQuery->fetch())
	{
		$programQuery->execute(array
		(
			":titleId" => $titleRow->id
		));
		$programRow = $programQuery->fetch();
		if ($programRow->title and $programRow->year)
		{
			$programs[$programRow->year][$programRow->title] = true;
		}
		
		$categories[$titleRow->category][] = $titleRow;
	}
	
	echo "
		<h2>Gefundene Titel</h2>
		<table id='notedirectory_table_titles' class='table {sortlist: [[0,0]]}'>
			<thead>
				<tr>
					<th>Titel</th>
					<th>Komponist</th>
					<th>Bearbeiter</th>
				</tr>
			</thead>
	";
	foreach ($categories as $category => $titles)
	{
		echo "
			<tbody class='tablesorter-infoOnly'>
				<tr>
					<th colspan='4'>" . $category . "</th>
				</tr>
			</tbody>
			<tbody>
		";
		foreach ($titles as $index => $row)
		{
			echo "
				<tr>
					<td>" . $row->title . "</td>
					<td>" . $row->composer . "</td>
					<td>" . $row->arranger . "</td>
				</tr>
			";
		}
		echo "</tbody>";
	}
	echo "
		</table>
		
		<h2>Programme welche diese Titel beinhalten</h2>
		<table id='notedirectory_table_programs' class='table {sortlist: [[1,0][0,0]]}'>
			<thead>
				<tr>
					<th>Typ</th>
					<th>Jahr</th>
				</tr>
			</thead>
			<tbody>
	";
	foreach ($programs as $year => $yearPrograms)
	{
		foreach ($yearPrograms as $title => $dummy)
		{
			echo "
				<tr>
					<td>" . $title . "</td>
					<td>" . $year . "</td>
				</tr>
			";
		}
	}
	echo "
			</tbody>
		</table>
	";
}
else
{
	if (Constants::$pagePath[2] == "all")
	{
		$query = Constants::$pdo->query("SELECT `notedirectory_titles`.`id` AS `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger` FROM `notedirectory_titles` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`");
	}
	else
	{
		$query = Constants::$pdo->prepare("SELECT `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger` FROM `notedirectory_programtitles` LEFT JOIN `notedirectory_titles` ON `notedirectory_titles`.`id` = `notedirectory_programtitles`.`titleId` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId` WHERE `programId` = :programId");
		$query->execute(array
		(
			":programId" => Constants::$pagePath[2]
		));
	}
	
	$categories = array();
	while ($row = $query->fetch())
	{
		$categories[$row->category][] = $row;
	}
	
	if (empty($categories))
	{
		echo "<div class='error'>Kein Programm ausgew&auml;hlt!</div>";
	}
	else
	{
		$headers = array("Nummer", "Titel", "Komponist", "Bearbeiter");
		if (Constants::$pagePath[2] == "all")
		{
			array_shift($headers);
		}
		echo "
			<table id='notedirectory_table_titles' class='table {sortlist: [[0,0]]}'>
				<thead>
					<tr>
		";
		foreach ($headers as $header)
		{
			echo "<th>" . $header . "</th>";
		}
		echo "
					</tr>
				</thead>
		";
		foreach ($categories as $category => $titles)
		{
			echo "
				<tbody class='tablesorter-infoOnly'>
					<tr>
						<th colspan='" . count($headers) . "'>" . $category . "</th>
					</tr>
				</tbody>
				<tbody>
			";
			foreach ($titles as $index => $row)
			{
				echo "<tr>";
				$cells = array($row->number, $row->title, $row->composer, $row->arranger);
				if (Constants::$pagePath[2] == "all")
				{
					array_shift($cells);
				}
				foreach ($cells as $cell)
				{
					echo "<td>" . $cell . "</td>";
				}
				echo "</tr>";
			}
			echo "</tbody>";
		}
		echo "</table>";
	}
}
?>