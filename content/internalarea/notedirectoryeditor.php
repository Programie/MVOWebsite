<h1>Notenverzeichniseditor</h1>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	if ($_POST["notedirectoryeditor_form_sendtoken"] == TokenManager::getSendToken("notedirectoryeditor"))
	{
		switch ($_POST["notedirectoryeditor_formtype"])
		{
			case "editcategory":
				$queryData = array
				(
					":title" => $_POST["notedirectoryeditor_form_title"]
				);

				if ($_POST["notedirectoryeditor_form_id"])
				{
					$queryData[":id"] = $_POST["notedirectoryeditor_form_id"];

					$query = Constants::$pdo->prepare("
						UPDATE `notedirectory_categories`
						SET `title` = :title
						WHERE `id` = :id
					");
				}
				else
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `notedirectory_categories`
						(`title`, `order`)
						SELECT :title, MAX(`order`) + 1 FROM `notedirectory_categories`
					");
				}

				$query->execute($queryData);

				echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				break;
			case "editprogram":
				$queryData = array
				(
					":typeId" => $_POST["notedirectoryeditor_form_type"],
					":year" => $_POST["notedirectoryeditor_form_year"]
				);

				if ($_POST["notedirectoryeditor_form_id"])
				{
					$queryData[":id"] = $_POST["notedirectoryeditor_form_id"];

					$query = Constants::$pdo->prepare("
						UPDATE `notedirectory_programs`
						SET
							`typeId` = :typeId,
							`year` = :year
						WHERE `id` = :id
					");
				}
				else
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `notedirectory_programs`
						(`typeId`, `year`)
						VALUES(:typeId, :year)
					");
				}

				$query->execute($queryData);

				echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				break;
			case "edittitle":
				$queryData = array
				(
					":categoryId" => $_POST["notedirectoryeditor_form_category"],
					":title" => $_POST["notedirectoryeditor_form_title"],
					":composer" => $_POST["notedirectoryeditor_form_composer"],
					":arranger" => $_POST["notedirectoryeditor_form_arranger"],
					":publisher" => $_POST["notedirectoryeditor_form_publisher"]
				);

				if ($_POST["notedirectoryeditor_form_id"])
				{
					$queryData[":id"] = $_POST["notedirectoryeditor_form_id"];

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
				}
				else
				{
					$query = Constants::$pdo->prepare("
						INSERT INTO `notedirectory_titles`
						(`categoryId`, `title`, `composer`, `arranger`, `publisher`)
						VALUES(:categoryId, :title, :composer, :arranger, :publisher)
					");
				}

				$query->execute($queryData);

				echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				break;
			case "sortcategories":
				$query = Constants::$pdo->prepare("
					UPDATE `notedirectory_categories`
					SET `order` = :order
					WHERE `id` = :id
				");

				foreach ($_POST as $key => $order)
				{
					if (substr($key, 0, 31) == "notedirectoryeditor_form_order_")
					{
						$query->execute(array
						(
							":id" => substr($key, 31),
							":order" => $order
						));
					}
				}

				echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				break;
		}
	}
	else
	{
		echo "<div class='alert-error'>Es wurde versucht, die &Auml;nderungen erneut zu &uuml;bernehmen!</div>";
	}
}

$newSendToken = TokenManager::getSendToken("notedirectoryeditor", true);
?>

<div id="notedirectoryeditor_tabs">
	<ul>
		<li><a href="#notedirectoryeditor_tabs_categories">Kategorien</a></li>
		<li><a href="#notedirectoryeditor_tabs_programs">Programme</a></li>
		<li><a href="#notedirectoryeditor_tabs_titles">Titel</a></li>
	</ul>
	<div id="notedirectoryeditor_tabs_categories">
		<div class="toolbar ui-widget-header ui-corner-all">
			<button id="notedirectoryeditor_categories_newbutton"><i class="el-icon-plus"></i> Neue Kategorie</button>
			<button id="notedirectoryeditor_categories_savebutton"><i class="el-icon-ok"></i> Reihenfolge Speichern</button>
		</div>

		<form id="notedirectoryeditor_categories_form" method="post">
			<input type="hidden" name="notedirectoryeditor_formtype" value="sortcategories"/>
			<input type="hidden" name="notedirectoryeditor_form_sendtoken" value="<?php echo $newSendToken;?>"/>

			<?php
			$categories = array();
			$query = Constants::$pdo->query("SELECT `id`, `title` FROM `notedirectory_categories` ORDER BY `order` ASC");
			while ($row = $query->fetch())
			{
				$categories[] = $row;
				echo "<input type='hidden' id='notedirectoryeditor_categories_" . $row->id . "' name='notedirectoryeditor_form_order_" . $row->id . "'/>";
			}
			?>
			<ul id="notedirectoryeditor_categories_list">
				<?php
				foreach ($categories as $row)
				{
					echo "<li class='ui-state-default' categoryid='" . $row->id . "'>" . escapeText($row->title) . "</li>";
				}
				?>
			</ul>
		</form>
	</div>
	<div id="notedirectoryeditor_tabs_programs">
		<div class="toolbar ui-widget-header ui-corner-all">
			<button id="notedirectoryeditor_programs_newbutton"><i class="el-icon-plus"></i> Neues Programm</button>
		</div>

		<table class="table {sortlist: [[0,1]]}">
			<thead>
				<tr>
					<th>Jahr</th>
					<th>Typ</th>
					<th>Anzahl Titel</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$query = Constants::$pdo->query("
					SELECT
						`notedirectory_programs`.`id`,
						`notedirectory_programs`.`year`,
						`notedirectory_programtypes`.`title` AS `type`,
						COUNT(`notedirectory_programtitles`.`id`) AS `titleCount`
					FROM `notedirectory_programs`
					LEFT JOIN `notedirectory_programtypes` ON `notedirectory_programtypes`.`id` = `notedirectory_programs`.`typeId`
					LEFT JOIN `notedirectory_programtitles` ON `notedirectory_programtitles`.`programId` = `notedirectory_programs`.`id`
					GROUP BY `notedirectory_programs`.`id`
				");
				while ($row = $query->fetch())
				{
					echo "
						<tr programid='" . $row->id . "'>
							<td>" . $row->year . "</td>
							<td>" . escapeText($row->type) . "</td>
							<td>" . $row->titleCount . "</td>
						</tr>
					";
				}
				?>
			</tbody>
		</table>
	</div>
	<div id="notedirectoryeditor_tabs_titles">
		<div class="toolbar ui-widget-header ui-corner-all">
			<button id="notedirectoryeditor_titles_newbutton"><i class="el-icon-plus"></i> Neuer Titel</button>
		</div>

		<table class="table {sortlist: [[0,0]]}">
			<thead>
				<tr>
					<th>Titel</th>
					<th>Komponist</th>
					<th>Bearbeiter</th>
					<th>Verleger</th>
				</tr>
			</thead>
			<?php
			$query = Constants::$pdo->query("
				SELECT
					`notedirectory_titles`.`id`,
					`notedirectory_titles`.`categoryId`,
					`notedirectory_categories`.`title` AS `category`,
					`notedirectory_titles`.`title`,
					`composer`,
					`arranger`,
					`publisher`
				FROM `notedirectory_titles`
				LEFT JOIN `notedirectory_categories` ON `notedirectory_categories`.`id` = `notedirectory_titles`.`categoryId`
				ORDER BY `notedirectory_categories`.`order` ASC
			");
			$categories = array();
			while ($row = $query->fetch())
			{
				$categories[$row->category][] = $row;
			}

			foreach ($categories as $category => $titles)
			{
				echo "
					<tbody class='tablesorter-infoOnly'>
						<tr>
							<th colspan='4'>" . escapeText($category) . "</th>
						</tr>
					</tbody>
					<tbody>
				";

				foreach ($titles as $row)
				{
					echo "
						<tr class='notedirectoryeditor_titles_row' categoryid='" . $row->categoryId . "' titleid='" . $row->id . "'>
							<td class='notedirectoryeditor_titles_row_title'>" . escapeText($row->title) . "</td>
							<td class='notedirectoryeditor_titles_row_composer'>" . escapeText($row->composer) . "</td>
							<td class='notedirectoryeditor_titles_row_arranger'>" . escapeText($row->arranger) . "</td>
							<td class='notedirectoryeditor_titles_row_publisher'>" . escapeText($row->publisher) . "</td>
						</tr>
					";
				}

				echo "</tbody>";
			}
			?>
		</table>
	</div>
</div>

<div id="notedirectoryeditor_editcategory" class="dialog">
	<form id="notedirectoryeditor_editcategory_form" action="#notedirectoryeditor_tabs_categories" method="post">
		<label class="input-label" for="notedirectoryeditor_editcategory_title">Titel</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-pencil"></i></span>
			<input class="input-field" type="text" id="notedirectoryeditor_editcategory_title" name="notedirectoryeditor_form_title"/>
		</div>

		<input type="hidden" name="notedirectoryeditor_formtype" value="editcategory"/>
		<input type="hidden" name="notedirectoryeditor_form_sendtoken" value="<?php echo $newSendToken;?>"/>
		<input type="hidden" id="notedirectoryeditor_editcategory_id" name="notedirectoryeditor_form_id"/>
	</form>
</div>

<div id="notedirectoryeditor_edittitle" class="dialog">
	<form id="notedirectoryeditor_edittitle_form" action="#notedirectoryeditor_tabs_titles" method="post">
		<label class="input-label" for="notedirectoryeditor_edittitle_title">Titel</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-pencil"></i></span>
			<input class="input-field" type="text" id="notedirectoryeditor_edittitle_title" name="notedirectoryeditor_form_title"/>
		</div>

		<label class="input-label" for="notedirectoryeditor_edittitle_composer">Komponist</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-person"></i></span>
			<input class="input-field" type="text" id="notedirectoryeditor_edittitle_composer" name="notedirectoryeditor_form_composer"/>
		</div>

		<label class="input-label" for="notedirectoryeditor_edittitle_arranger">Bearbeiter</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-person"></i></span>
			<input class="input-field" type="text" id="notedirectoryeditor_edittitle_arranger" name="notedirectoryeditor_form_arranger"/>
		</div>

		<label class="input-label" for="notedirectoryeditor_edittitle_publisher">Verleger</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-group"></i></span>
			<input class="input-field" type="text" id="notedirectoryeditor_edittitle_publisher" name="notedirectoryeditor_form_publisher"/>
		</div>

		<label class="input-label" for="notedirectoryeditor_edittitle_category">Kategorie</label>
		<div class="input-container">
			<span class="input-addon"><i class="el-icon-folder-open"></i></span>
			<select class="input-field" id="notedirectoryeditor_edittitle_category" name="notedirectoryeditor_form_category">
				<?php
				$query = Constants::$pdo->query("SELECT `id`, `title` FROM `notedirectory_categories` ORDER BY `title` ASC");
				while ($row = $query->fetch())
				{
					echo "<option value='" . $row->id . "'>" . escapeText($row->title) . "</option>";
				}
				?>
			</select>
		</div>

		<input type="hidden" name="notedirectoryeditor_formtype" value="edittitle"/>
		<input type="hidden" name="notedirectoryeditor_form_sendtoken" value="<?php echo $newSendToken;?>"/>
		<input type="hidden" id="notedirectoryeditor_edittitle_id" name="notedirectoryeditor_form_id"/>
	</form>
</div>

<script type="text/javascript">
	$("#notedirectoryeditor_tabs").tabs();
	$("#notedirectoryeditor_categories_list").sortable();

	$("#notedirectoryeditor_categories_list").find("li").click(function()
	{
		$("#notedirectoryeditor_editcategory_form")[0].reset();

		$("#notedirectoryeditor_editcategory_id").val($(this).attr("categoryid"));
		$("#notedirectoryeditor_editcategory_title").val($(this).text());

		$("#notedirectoryeditor_editcategory").dialog("option", "title", "Kategorie bearbeiten");
		$("#notedirectoryeditor_editcategory").dialog("open");
	});

	$("#notedirectoryeditor_categories_newbutton").click(function()
	{
		$("#notedirectoryeditor_editcategory_form")[0].reset();

		$("#notedirectoryeditor_editcategory_id").val(0);

		$("#notedirectoryeditor_editcategory").dialog("option", "title", $("#notedirectoryeditor_categories_newbutton").text());
		$("#notedirectoryeditor_editcategory").dialog("open");
	});

	$("#notedirectoryeditor_categories_savebutton").click(function()
	{
		$("#notedirectoryeditor_categories_list").find("li").each(function()
		{
			$("#notedirectoryeditor_categories_" + $(this).attr("categoryid")).val($(this).index());
		});
		$("#notedirectoryeditor_categories_form").submit();
	});

	$("#notedirectoryeditor_titles_newbutton").click(function()
	{
		$("#notedirectoryeditor_edittitle_form")[0].reset();

		$("#notedirectoryeditor_edittitle_id").val(0);

		$("#notedirectoryeditor_edittitle").dialog("option", "title", $("#notedirectoryeditor_titles_newbutton").text());
		$("#notedirectoryeditor_edittitle").dialog("open");
	});

	$(".notedirectoryeditor_titles_row").click(function()
	{
		$("#notedirectoryeditor_edittitle_form")[0].reset();

		$("#notedirectoryeditor_edittitle_id").val($(this).attr("titleid"));
		$("#notedirectoryeditor_edittitle_category").find("option[value=" + $(this).attr("categoryid") + "]").prop("selected", true);
		$("#notedirectoryeditor_edittitle_title").val($(this).find(".notedirectoryeditor_titles_row_title").text());
		$("#notedirectoryeditor_edittitle_composer").val($(this).find(".notedirectoryeditor_titles_row_composer").text());
		$("#notedirectoryeditor_edittitle_arranger").val($(this).find(".notedirectoryeditor_titles_row_arranger").text());
		$("#notedirectoryeditor_edittitle_publisher").val($(this).find(".notedirectoryeditor_titles_row_publisher").text());

		$("#notedirectoryeditor_edittitle").dialog("option", "title", "Titel bearbeiten");
		$("#notedirectoryeditor_edittitle").dialog("open");
	});

	$("#notedirectoryeditor_editcategory").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		modal: true,
		resizable : false,
		width: 400,
		buttons:
		{
			"OK": function ()
			{
				if ($("#notedirectoryeditor_editcategory_title").val())
				{
					$("#notedirectoryeditor_editcategory_form").submit();
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

	$("#notedirectoryeditor_edittitle").dialog(
	{
		autoOpen: false,
		closeText: "Schlie&szlig;en",
		//height: 600,
		minWidth: 500,
		modal: true,
		width: 800,
		buttons:
		{
			"OK": function ()
			{
				if ($("#notedirectoryeditor_edittitle_title").val())
				{
					$("#notedirectoryeditor_edittitle_form").submit();
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
</script>