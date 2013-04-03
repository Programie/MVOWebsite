<?php
$title = array("Notenverzeichnis");
$showInGroups = true;
if ($_POST["notedirectory_searchstring"])
{
	$title[] = "Suchergebnisse";
}
else
{
	if (Constants::$pagePath[2] == "all")
	{
		if (Constants::$accountManager->hasPermission("notedirectory.view.all"))
		{
			$title[] = "Alle Titel";
		}
	}
	else
	{
		if (!Constants::$pagePath[2])
		{
			if (Constants::$accountManager->hasPermission("notedirectory.view.programs"))
			{
				$query = Constants::$pdo->query("SELECT `notedirectory_programs`.`id` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId` WHERE `notedirectory_programs`.`year` = YEAR(NOW()) AND `notedirectory_programtypes`.`showNoSelection`");
				if ($query->rowCount())
				{
					$row = $query->fetch();
					Constants::$pagePath[2] = $row->id;
				}
			}
		}
		if (Constants::$pagePath[2])
		{
			if (Constants::$accountManager->hasPermission("notedirectory.view.programs"))
			{
				$query = Constants::$pdo->prepare("SELECT `title`, `showInGroups`, `year` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId` WHERE `notedirectory_programs`.`id` = :id");
				$query->execute(array
				(
					":id" => Constants::$pagePath[2]
				));
				if ($query->rowCount())
				{
					$row = $query->fetch();
					$title[] = escapeText($row->title) . " " . $row->year;
					$showInGroups = $row->showInGroups;
				}
			}
		}
	}
}
echo "<h1>" . implode(" - ", $title) . "</h1>";
?>

<ul id="notedirectory_selectionmenu" class="menu no-print">
	<li>
		<a href="#">Auswahl</a>
		<ul>
			<?php
			if (Constants::$accountManager->hasPermission("notedirectory.view.programs"))
			{
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
						echo "<li><a href='/internalarea/notedirectory/" . $id . "'>" . escapeText($title) . "</a></li>";
					}
					echo "
							</ul>
						</li>
					";
				}
			}
			if (Constants::$accountManager->hasPermission("notedirectory.view.all"))
			{
				echo "<li><a href='/internalarea/notedirectory/all'>Alle</a></li>";
			}
			?>
		</ul>
	</li>
</ul>

<div id="notedirectory_options_div1" class="no-print">
	<div id="notedirectory_options_div2">
		<form id="notedirectory_searchform" action="/internalarea/notedirectory" method="post">
			<input type="text" class="input-search" id="notedirectory_searchstring" name="notedirectory_searchstring" placeholder="Suchbegriff" value="<?php echo htmlspecialchars($_POST["notedirectory_searchstring"]);?>"/>
		</form>
	</div>
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
	if (Constants::$pagePath[2])
	{
		if (Constants::$pagePath[2] == "details")
		{
			$query = Constants::$pdo->prepare("SELECT `title` FROM `notedirectory_titles` WHERE `id` = :id");
			$query->execute(array
			(
				":id" => Constants::$pagePath[3]
			));
			if ($query->rowCount())
			{
				$row = $query->fetch();
				echo "<h2>Programme welche den Titel <i>" . escapeText($row->title) . "</i> enthalten</h2>";
				$query = Constants::$pdo->prepare("
					SELECT `notedirectory_programs`.`id`, `year`, `title`, `number`
					FROM `notedirectory_programtitles`
					LEFT JOIN `notedirectory_programs` ON `notedirectory_programs`.`id` = `notedirectory_programtitles`.`programId`
					LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`
					WHERE `notedirectory_programtitles`.`titleId` = :id
				");
				$query->execute(array
				(
					":id" => Constants::$pagePath[3]
				));
				if ($query->rowCount())
				{
					echo "
						<div class='info no-print'>Klicke auf ein Programm um das vollst&auml;ndige Programm anzuzeigen.</div>
						<table class='table {sortlist: [[0,1],[1,0]]}'>
							<thead>
								<tr>
									<th>Jahr</th>
									<th>Titel</th>
									<th>Nummer</th>
								</tr>
							</thead>
							<tbody>
					";
					while ($row = $query->fetch())
					{
						echo "
							<tr class='pointer' onclick=\"document.location.href='/internalarea/notedirectory/" . $row->id . "';\">
								<td>" . $row->year . "</td>
								<td>" . escapeText($row->title) . "</td>
								<td>" . $row->number . "</td>
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
					echo "<div class='error'>Keine Programme gefunden!</div>";
				}
			}
			else
			{
				echo "<div class='error'>Titel nicht gefunden!</div>";
			}
		}
		else
		{
			$query = null;
			if (Constants::$pagePath[2] == "all")
			{
				if (Constants::$accountManager->hasPermission("notedirectory.view.all"))
				{
					$query = Constants::$pdo->query("SELECT `notedirectory_titles`.`id`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher` FROM `notedirectory_titles` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`");
				}
			}
			else
			{
				if (Constants::$pagePath[2])
				{
					if (Constants::$accountManager->hasPermission("notedirectory.view.programs"))
					{
					
						$query = Constants::$pdo->prepare("SELECT `notedirectory_titles`.`id`, `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher` FROM `notedirectory_programtitles` LEFT JOIN `notedirectory_titles` ON `notedirectory_titles`.`id` = `notedirectory_programtitles`.`titleId` LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId` WHERE `programId` = :programId");
						$query->execute(array
						(
							":programId" => Constants::$pagePath[2]
						));
					}
				}
			}
			
			if ($query and $query->rowCount())
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
				echo "<div class='error'>Keine Titel vorhanden!</div>";
			}
		}
	}
	else
	{
		echo "<div class='error'>Kein Programm ausgew&auml;hlt!</div>";
	}
}
?>