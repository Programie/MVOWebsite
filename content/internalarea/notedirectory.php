<?php
$title = "Notenverzeichnis";
$showInGroups = true;
if (Constants::$pagePath[2] and Constants::$pagePath[2] != "all")
{
	$query = Constants::$pdo->prepare("SELECT `title`, `showInGroups`, `year` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId` WHERE `notedirectory_programs`.`id` = :id");
	$query->execute(array
	(
		":id" => Constants::$pagePath[2]
	));
	if ($query->rowCount())
	{
		$row = $query->fetch();
		$title .= " - " . $row->title . " " . $row->year;
		$showInGroups = $row->showInGroups;
	}
}
echo "<h1>" . $title . "</h1>";
?>

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

<div id="notedirectory_searchform_div1" class="no-print">
	<form id="notedirectory_searchform" action="/internalarea/notedirectory" method="post">
		<input type="text" class="input-search" id="notedirectory_searchstring" name="notedirectory_searchstring" placeholder="Suchbegriff" value="<?php echo htmlspecialchars($_POST["notedirectory_searchstring"]);?>"/>
	</form>
</div>

<?php
if ($_POST["notedirectory_searchstring"])
{
	$query = Constants::$pdo->prepare("
		SELECT
			`notedirectory_titles`.`id`,
			`notedirectory_categories`.`title` AS `category`,
			`notedirectory_titles`.`title`,
			`composer`,
			`arranger`,
			`publisher`
		FROM `notedirectory_titles`
		LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`
		WHERE
			`notedirectory_titles`.`title` LIKE :searchstring OR
			`notedirectory_titles`.`composer` LIKE :searchstring OR
			`notedirectory_titles`.`arranger` LIKE :searchstring OR
			`notedirectory_titles`.`publisher` LIKE :searchstring OR
			`notedirectory_categories`.`title` LIKE :searchstring
	");
	$query->execute(array
	(
		":searchstring" => "%" . $_POST["notedirectory_searchstring"] . "%"
	));
	
	$titles = $query->fetchAll();
	
	echo "<h2>Gefundene Titel</h2>";
	$columns = array
	(
		"title" => "Titel",
		"composer" => "Komponist",
		"arranger" => "Bearbeiter",
		"publisher" => "Verleger"
	);
	new NoteDirectory($columns, $titles, $showInGroups);
}
else
{
	if (Constants::$pagePath[2] == "all")
	{
		$query = Constants::$pdo->query("SELECT `notedirectory_titles`.`id` AS `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher` FROM `notedirectory_titles` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`");
	}
	else
	{
		$query = Constants::$pdo->prepare("SELECT `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher` FROM `notedirectory_programtitles` LEFT JOIN `notedirectory_titles` ON `notedirectory_titles`.`id` = `notedirectory_programtitles`.`titleId` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId` WHERE `programId` = :programId");
		$query->execute(array
		(
			":programId" => Constants::$pagePath[2]
		));
	}
	
	if ($query->rowCount())
	{
		$titles = $query->fetchAll();
		if (Constants::$pagePath[2] == "all")
		{
			$columns = array
			(
				"title" => "Titel",
				"composer" => "Komponist",
				"arranger" => "Bearbeiter",
				"publisher" => "Verleger"
			);
		}
		else
		{
			$columns = array
			(
				"number" => "Nummer",
				"title" => "Titel",
				"composer" => "Komponist",
				"arranger" => "Bearbeiter",
				"publisher" => "Verleger"
			);
		}
		new NoteDirectory($columns, $titles, $showInGroups);
	}
	else
	{
		echo "<div class='error'>Kein Programm ausgew&auml;hlt!</div>";
	}
}
?>