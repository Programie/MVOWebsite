<?php
$title = array("Notenverzeichnis");
$showInGroups = true;
switch (Constants::$pagePath[2])
{
	case "all":
		$title[] = "Alle Titel";
		break;
	case "category":
		$query = Constants::$pdo->prepare("SELECT `title` FROM `notedirectory_categories` WHERE `id` = :id");
		$query->execute(array(":id" => Constants::$pagePath[3]));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			$title[] = escapeText($row->title);
		}
		break;
	case "details":
		$title[] = "Titeldetails";
		break;
	case "program":
		$query = Constants::$pdo->prepare("SELECT `title`, `showInGroups`, `year` FROM `notedirectory_programs` LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId` WHERE `notedirectory_programs`.`id` = :id");
		$query->execute(array(":id" => Constants::$pagePath[3]));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			$title[] = escapeText($row->title) . " " . $row->year;
			$showInGroups = $row->showInGroups;
		}
		break;
	case "search":
		$title[] = "Suchergebnisse";
		break;
}
echo "<h1>" . implode(" - ", $title) . "</h1>";
?>

	<ul id="notedirectory_selectionmenu" class="menu no-print">
		<li>
			<a href="#">Auswahl</a>
			<ul>
				<li>
					<a href="#">Programme</a>
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
								echo "<li><a href='/internalarea/notedirectory/program/" . $id . "'>" . escapeText($title) . "</a></li>";
							}
							echo "
									</ul>
								</li>
							";
						}
						?>
					</ul>
				</li>
				<li>
					<a href="#">Kategorien</a>
					<ul>
						<?php
						$query = Constants::$pdo->query("SELECT `id`, `title` FROM `notedirectory_categories` ORDER BY `title` ASC");
						while ($row = $query->fetch())
						{
							echo "<li><a href='/internalarea/notedirectory/category/" . $row->id . "'>" . escapeText($row->title) . "</a></li>";
						}
						?>
					</ul>
				</li>
				<li><a href="/internalarea/notedirectory/all">Alle Titel</a></li>
			</ul>
		</li>
	</ul>

	<div id="notedirectory_options_div1" class="no-print">
		<div id="notedirectory_options_div2">
			<form id="notedirectory_searchform" action="/internalarea/notedirectory" method="get">
				<div class="input-container">
					<span class="input-addon"><i class="el-icon-search"></i></span>
					<input class="input-field" type="text" id="notedirectory_searchstring" name="notedirectory_searchstring" placeholder="Suchbegriff" value="<?php echo escapeText($_POST["notedirectory_searchstring"]); ?>"/>
				</div>
			</form>
		</div>
	</div>
<?php
if (Constants::$accountManager->hasPermission("notedirectory.edit"))
{
	$previousEditSendToken = TokenManager::getSendToken("notedirectory_edit");
	$newEditSendToken = TokenManager::getSendToken("notedirectory_edit", true);
?>
	<div id="notedirectory_edittitle">
		<form id="notedirectory_edittitle_form" method="post" onsubmit="return false;">
			<label class="input-label" for="notedirectory_edittitle_title">Titel</label>
			<div class="input-container">
				<span class="input-addon"><i class="el-icon-pencil"></i></span>
				<input class="input-field" type="text" id="notedirectory_edittitle_title" name="notedirectory_edittitle_title"/>
			</div>

			<label class="input-label" for="notedirectory_edittitle_composer">Komponist</label>
			<div class="input-container">
				<span class="input-addon"><i class="el-icon-person"></i></span>
				<input class="input-field" type="text" id="notedirectory_edittitle_composer" name="notedirectory_edittitle_composer"/>
			</div>

			<label class="input-label" for="notedirectory_edittitle_arranger">Bearbeiter</label>
			<div class="input-container">
				<span class="input-addon"><i class="el-icon-person"></i></span>
				<input class="input-field" type="text" id="notedirectory_edittitle_arranger" name="notedirectory_edittitle_arranger"/>
			</div>

			<label class="input-label" for="notedirectory_edittitle_publisher">Verleger</label>
			<div class="input-container">
				<span class="input-addon"><i class="el-icon-group"></i></span>
				<input class="input-field" type="text" id="notedirectory_edittitle_publisher" name="notedirectory_edittitle_publisher"/>
			</div>

			<label class="input-label" for="notedirectory_edittitle_category">Kategorie</label>
			<div class="input-container">
				<span class="input-addon"><i class="el-icon-folder-open"></i></span>
				<select class="input-field" id="notedirectory_edittitle_category" name="notedirectory_edittitle_category">
					<?php
					$query = Constants::$pdo->query("SELECT `id`, `title` FROM `notedirectory_categories` ORDER BY `title` ASC");
					while ($row = $query->fetch())
					{
						echo "<option value='" . $row->id . "'>" . escapeText($row->title) . "</option>";
					}
					?>
				</select>
			</div>

			<input type="hidden" id="notedirectory_edittitle_sendtoken" name="notedirectory_edittitle_sendtoken" value="<?php echo $newEditSendToken;?>"/>
			<input type="hidden" id="notedirectory_edittitle_id" name="notedirectory_edittitle_id"/>
		</form>
	</div>
<?php
}

switch (Constants::$pagePath[2])
{
	case "all":
		$query = Constants::$pdo->query("
			SELECT `notedirectory_titles`.`id`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher`
			FROM `notedirectory_titles`
			LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`
			ORDER BY `notedirectory_titles`.`categoryId` ASC
		");
		if ($query->rowCount())
		{
			$columns = array("title" => "Titel", "composer" => "Komponist", "arranger" => "Bearbeiter", "publisher" => "Verleger");
			$noteDirectory = new NoteDirectory();
			$noteDirectory->setColumns($columns);
			$noteDirectory->setTitles($query->fetchAll());
			$noteDirectory->setShowInGroups(true);
			$noteDirectory->createList();
		}
		else
		{
			echo "<div class='alert-error'>Kein Titel vorhanden!</div>";
		}
		break;
	case "category":
		$query = Constants::$pdo->prepare("SELECT `id`, `title`, `composer`, `arranger`, `publisher` FROM `notedirectory_titles` WHERE `categoryId` = :categoryId");
		$query->execute(array(":categoryId" => Constants::$pagePath[3]));
		if ($query->rowCount())
		{
			$columns = array("title" => "Titel", "composer" => "Komponist", "arranger" => "Bearbeiter", "publisher" => "Verleger");
			$noteDirectory = new NoteDirectory();
			$noteDirectory->setColumns($columns);
			$noteDirectory->setTitles($query->fetchAll());
			$noteDirectory->setShowInGroups(false);
			$noteDirectory->createList();
		}
		else
		{
			echo "<div class='alert-error'>Kein Titel vorhanden!</div>";
		}
		break;
	case "details":
		if (isset($_POST["notedirectory_edittitle_id"]) and Constants::$accountManager->hasPermission("notedirectory.edit"))
		{
			if ($_POST["notedirectory_edittitle_sendtoken"] == $previousEditSendToken)
			{
				if ($_POST["notedirectory_edittitle_id"])
				{
					$query = Constants::$pdo->prepare("
						UPDATE `notedirectory_titles`
						SET
							`categoryId` = :categoryId,
							`title` = :title,
							`composer` = :composer,
							`arranger` = :arranger,
							`publisher` = :publisher
						WHERE `id` = :id
					");
					$query->execute(array
					(
						":id" => $_POST["notedirectory_edittitle_id"],
						":categoryId" => $_POST["notedirectory_edittitle_category"],
						":title" => $_POST["notedirectory_edittitle_title"],
						":composer" => $_POST["notedirectory_edittitle_composer"],
						":arranger" => $_POST["notedirectory_edittitle_arranger"],
						":publisher" => $_POST["notedirectory_edittitle_publisher"],
					));
				}
				else
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `notedirectory_titles`
						(`categoryId`, `title`, `composer`, `arranger`, `publisher`)
						VALUES(:categoryId, :title, :composer, :arranger, :publisher)
					");
					$query->execute(array
					(
						":categoryId" => $_POST["notedirectory_edittitle_category"],
						":title" => $_POST["notedirectory_edittitle_title"],
						":composer" => $_POST["notedirectory_edittitle_composer"],
						":arranger" => $_POST["notedirectory_edittitle_arranger"],
						":publisher" => $_POST["notedirectory_edittitle_publisher"],
					));
				}
				echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
			}
			else
			{
				echo "<div class='alert-error'>Es wurde versucht, die &Auml;nderungen erneut zu &uuml;bernehmen!</div>";
			}
		}

		$query = Constants::$pdo->prepare("SELECT `id`, `categoryId`, `title`, `composer`, `arranger`, `publisher` FROM `notedirectory_titles` WHERE `id` = :id");
		$query->execute(array(":id" => Constants::$pagePath[3]));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			if (Constants::$accountManager->hasPermission("notedirectory.edit"))
			{
				echo "
					<button id='notedirectory_edittitle_button'>Titel bearbeiten</button>
					<div id='notedirectory_hiddeninfo'>
						<id>" . escapeText($row->id) . "</id>
						<categoryId>" . escapeText($row->categoryId) . "</categoryId>
						<title>" . escapeText($row->title) . "</title>
						<composer>" . escapeText($row->composer) . "</composer>
						<arranger>" . escapeText($row->arranger) . "</arranger>
						<publisher>" . escapeText($row->publisher) . "</publisher>
					</div>
				";
			}
			echo "<h2>Programme welche den Titel <i>" . escapeText($row->title) . "</i> enthalten</h2>";
			$query = Constants::$pdo->prepare("
				SELECT `notedirectory_programs`.`id`, `year`, `title`, `number`
				FROM `notedirectory_programtitles`
				LEFT JOIN `notedirectory_programs` ON `notedirectory_programs`.`id` = `notedirectory_programtitles`.`programId`
				LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`
				WHERE `notedirectory_programtitles`.`titleId` = :id
			");
			$query->execute(array(":id" => Constants::$pagePath[3]));
			if ($query->rowCount())
			{
				echo "
					<div class='alert-info no-print'>Klicke auf ein Programm um das vollst&auml;ndige Programm anzuzeigen.</div>
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
						<tr class='pointer' onclick=\"document.location.href='/internalarea/notedirectory/program/" . $row->id . "';\">
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
				echo "<div class='alert-error'>Kein Programm gefunden!</div>";
			}
		}
		else
		{
			echo "<div class='alert-error'>Titel nicht gefunden!</div>";
		}
		break;
	case "program":
		$query = Constants::$pdo->prepare("
			SELECT `notedirectory_titles`.`id`, `number`, `notedirectory_categories`.`title` AS `category`, `notedirectory_titles`.`title`, `composer`, `arranger`, `publisher`
			FROM `notedirectory_programtitles`
			LEFT JOIN `notedirectory_titles` ON `notedirectory_titles`.`id` = `notedirectory_programtitles`.`titleId`
			LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`
			WHERE `programId` = :programId
			ORDER BY `notedirectory_titles`.`categoryId` ASC
		");
		$query->execute(array(":programId" => Constants::$pagePath[3]));
		if ($query->rowCount())
		{
			$columns = array("number" => "Nummer", "title" => "Titel", "composer" => "Komponist", "arranger" => "Bearbeiter", "publisher" => "Verleger");
			$noteDirectory = new NoteDirectory();
			$noteDirectory->setColumns($columns);
			$noteDirectory->setTitles($query->fetchAll());
			$noteDirectory->setShowInGroups($showInGroups);
			$noteDirectory->createList();
		}
		else
		{
			echo "<div class='alert-error'>Kein Titel vorhanden!</div>";
		}
		break;
	case "search":
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
			ORDER BY `notedirectory_titles`.`categoryId` ASC
		");
		$query->execute(array(":searchstring" => "%" . Constants::$pagePath[3] . "%"));

		$noteDirectory = new NoteDirectory();
		$noteDirectory->setColumns(array("title" => "Titel", "composer" => "Komponist", "arranger" => "Bearbeiter", "publisher" => "Verleger"));
		$noteDirectory->setTitles($query->fetchAll());
		$noteDirectory->setHighlight(Constants::$pagePath[3]);
		$noteDirectory->setShowInGroups(true);
		$noteDirectory->createList();
		break;
}
?>

<script type="text/javascript">
	$("#notedirectory_searchform").submit(function()
	{
		var searchString = $("#notedirectory_searchstring").val();
		if (searchString)
		{
			document.location = "/internalarea/notedirectory/search/" + encodeURIComponent(searchString);
		}
		return false;
	});
<?php
if (Constants::$accountManager->hasPermission("notedirectory.edit"))
{
?>
	$("#notedirectory_edittitle").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		height: 600,
		minWidth: 500,
		modal: true,
		width: 800,
		buttons:
		{
			"OK": function ()
			{
				if ($("#notedirectory_edittitle_title").val())
				{
					$("#notedirectory_edittitle_form")[0].submit();
				}
				else
				{
					alert("Kein Titel angegeben!");
				}
			},
			"Abbrechen": function ()
			{
				$(this).dialog("close");
			}
		}
	});

	$("#notedirectory_edittitle_button").click(function ()
	{
		var hiddenInfoElement = $("#notedirectory_hiddeninfo");
		$("#notedirectory_edittitle_form")[0].reset();
		$("#notedirectory_edittitle_id").val(hiddenInfoElement.find("id").text());
		$("#notedirectory_edittitle_category").find("option[value=" + hiddenInfoElement.find("categoryId").text() + "]").prop("selected", true);
		$("#notedirectory_edittitle_title").val(hiddenInfoElement.find("title").text());
		$("#notedirectory_edittitle_composer").val(hiddenInfoElement.find("composer").text());
		$("#notedirectory_edittitle_arranger").val(hiddenInfoElement.find("arranger").text());
		$("#notedirectory_edittitle_publisher").val(hiddenInfoElement.find("publisher").text());
		$("#notedirectory_edittitle").dialog("option", "title", $("#notedirectory_edittitle_button").text());
		$("#notedirectory_edittitle").dialog("open");
	});
<?php
}
?>
</script>