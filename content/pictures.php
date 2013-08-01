<h1>Fotogalerie</h1>

<?php
if (isset($_POST["pictures_edit_id"]) and Constants::$accountManager->hasPermission("pictures.edit"))
{
	$date = explode(".", $_POST["pictures_edit_date"]);
	if (@checkdate($date[1], $date[0], $date[2]))
	{
		$query = Constants::$pdo->prepare("
			UPDATE `picturealbums`
			SET `published` = :published, `date` = :date, `isPublic` = :isPublic, `title` = :title, `text` = :text
			WHERE `id` = :id
		");
		$query->execute(array
		(
			":published" => $_POST["pictures_edit_options_published"] ? 1 : 0,
			":date" => $date[2] . "-" . $date[1] . "-" . $date[0],
			":isPublic" => $_POST["pictures_edit_options_public"] ? 1 : 0,
			":title" => $_POST["pictures_edit_title"],
			":text" => $_POST["pictures_edit_text"],
			":id" => $_POST["pictures_edit_id"]
		));

		if ($_POST["pictures_edit_options_albumOfTheYear"])
		{
			$setAlbumOfTheYear = true;
		}
		else
		{
			$setAlbumOfTheYear = false;
		}
		Pictures::updateAlbumOfTheYear($date[2], $_POST["pictures_edit_id"], $setAlbumOfTheYear);

		echo "<div class='alert-success'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
	}
	else
	{
		echo "<div class='alert-error'>Das Datum ist ung&uuml;ltig!</div>";
	}
}

$showIndex = true;

switch (Constants::$pagePath[1])
{
	case "album":
		$albumData = Pictures::getPictures(Constants::$pagePath[2]);
		if ($albumData)
		{
			$albumFound = true;
			$yearFound = true;
			$path = "files/pictures/" . Constants::$pagePath[2];

			echo "<h2>" . $albumData->title . "</h2>";

			if (!$albumData->published)
			{
				echo "<div class='alert-info'>Dieses Album ist noch nicht freigegeben!</div>";
			}

			if (Constants::$accountManager->hasPermission("pictures.edit"))
			{
				echo "<button type='button' onclick='pictures_editAlbum(" . Constants::$pagePath[2] . ");'>Album bearbeiten</button>";
			}

			echo "<p>Datum: " . date("d.m.Y", strtotime($albumData->date)) . "</p>";

			if ($albumData->text)
			{
				echo "<p id='pictures_text'>" . formatText($albumData->text) . "</p>";
			}

			echo "<ul id='gallery' class='polaroids'>";
			foreach ($albumData->pictures as $pictureData)
			{
				echo "
					<li>
						<a href='/" . $path . "/large_" . $pictureData->fileId . ".jpg' caption='" . $pictureData->text . "' number='" . $pictureData->number . "'>
							<img src='/" . $path . "/small_" . $pictureData->fileId . ".jpg' alt='" . ($pictureData->text ? $pictureData->text : " ") . "'/>
						</a>
					</li>
				";
			}
			echo "
				</ul>

				<div class='clear'></div>

				<div id='pictures_edit_contextmenu'>
					<ul>
						<li id='pictures_edit_contextmenu_edittitle'><i class='icon-pencil'></i> Titel bearbeiten</li>
						<li id='pictures_edit_contextmenu_setcover'><i class='icon-picture'></i> Als Cover verwenden</li>
					</ul>
				</div>

				<script type='text/javascript'>
					$('#gallery').photobox('li > a',
					{
						history : false,
						loop : false,
						time : 10000
					});
				</script>
			";

			$showIndex = false;
		}
		else
		{
			echo "<div class='alert-error'>Das Album wurde nicht gefunden!</div>";
		}
		break;
	case "year":
		$albums = Pictures::getAlbums(Constants::$pagePath[2]);
		if ($albums)
		{
			echo "<h2>Jahr " . Constants::$pagePath[2] . "</h2>";

			echo "<ul class='polaroids'>";
			foreach ($albums as $albumData)
			{
				echo "
					<li>
						<a href='/pictures/album/" . $albumData->id . "' caption='" . $albumData->title . "' " . ($albumData->published ? "" : "class='pictures_unpublished' title='Dieses Album ist noch nicht freigegeben'") . ">
							<img src='/files/pictures/" . $albumData->id . "/small_" . $albumData->coverPictureFileId . ".jpg'/>
						</a>
					</li>
				";
			}
			echo "</ul>";

			echo "<div class='clear'></div>";

			$showIndex = false;
		}
		else
		{
			echo "<div class='alert-error'>In dem ausgew&auml;hlten Jahr befinden sich keine Alben!</div>";
		}
		break;
}

if ($showIndex)
{
	if (Constants::$accountManager->hasPermission("pictures.edit"))
	{
		$query = Constants::$pdo->query("SELECT `id`, `date`, `title` FROM `picturealbums` WHERE NOT `published`");
		if ($query->rowCount())
		{
			echo "
				<div class='alert-info'>
					<p><b>Die folgenden Alben wurden noch nicht freigegeben:</b></p>

					<ul>
			";
			while ($row = $query->fetch())
			{
				$date = explode("-", $row->date);
				echo "<li onclick='pictures_editAlbum(" . $row->id . ");' style='cursor: pointer;' title='Klicken zum Bearbeiten'>" . $row->title . " (" . $date[0] . ")</li>";
			}
			echo "
					</ul>
				</div>
			";
		}
	}

	$years = Pictures::getYears();
	krsort($years, SORT_NUMERIC);

	$query = Constants::$pdo->prepare("
	 	SELECT `fileId`
		FROM `picturealbums`
		LEFT JOIN `pictures` ON `pictures`.`albumId` = `picturealbums`.`id` AND `pictures`.`number` = `picturealbums`.`coverPicture`
		WHERE `picturealbums`.`id` = :id
	");

	echo "<ul class='polaroids'>";
	foreach ($years as $year => $yearData)
	{
		$query->execute(array(":id" => $yearData->coverAlbumId));
		$row = $query->fetch();
		echo "
			<li>
				<a href='/pictures/year/" . $year . "' caption='" . $year . "'>
					<img src='/files/pictures/" . $yearData->coverAlbumId . "/small_" . $row->fileId . ".jpg'/>
				</a>
			</li>
		";
	}
	echo "</ul>";

	echo "<div class='clear'></div>";
}

if (Constants::$accountManager->hasPermission("pictures.edit"))
{
	?>
	<div id="pictures_edit">
		<form id="pictures_edit_form" method="post" onsubmit="return false">
			<input type="hidden" id="pictures_edit_id" name="pictures_edit_id"/>
			<label class="input-label" for="pictures_edit_title">Titel</label>
			<div class="input-container">
				<span class="input-addon"><i class="icon-pencil"></i></span>
				<input class="input-field" type="text" id="pictures_edit_title" name="pictures_edit_title" required/>
			</div>

			<label class="input-label" for="pictures_edit_date">Datum</label>
			<div class="input-container">
				<span class="input-addon"><i class="icon-calendar"></i></span>
				<input class="input-field date" type="text" id="pictures_edit_date" name="pictures_edit_date" required/>
			</div>

			<fieldset id="pictures_edit_options">
				<legend>Optionen</legend>

				<div><input type="checkbox" id="pictures_edit_options_published" name="pictures_edit_options_published"/><label for="pictures_edit_options_published">Freigeben</label></div>
				<div><input type="checkbox" id="pictures_edit_options_public" name="pictures_edit_options_public"/><label for="pictures_edit_options_public">&Ouml;ffentlich</label></div>
				<div><input type="checkbox" id="pictures_edit_options_albumOfTheYear" name="pictures_edit_options_albumOfTheYear"/><label for="pictures_edit_options_albumOfTheYear">Album des Jahres</label></div>
			</fieldset>

			<fieldset>
				<legend>Text</legend>

				<textarea id="pictures_edit_text" name="pictures_edit_text" rows="15" cols="15"></textarea>
			</fieldset>
		</form>
	</div>

	<script type="text/javascript">
		function pictures_editAlbum(id)
		{
			$("#pictures_edit_id").val(id);
			$("#pictures_edit").dialog("open");
			$.ajax(
			{
				type: "GET",
				dataType: "json",
				url: "/pictures/getalbumdetails/" + id,
				error: function (jqXhr, textStatus, errorThrown)
				{
					alert("Fehler beim Laden der Albumdaten!");
				},
				success: function (data, status, jqXhr)
				{
					$("#pictures_edit_title").val(data.title);
					$("#pictures_edit_date").datepicker("setDate", new Date(data.date));
					$("#pictures_edit_options_published").prop("checked", data.published);
					$("#pictures_edit_options_public").prop("checked", data.isPublic);
					$("#pictures_edit_options_albumOfTheYear").prop("checked", data.albumOfTheYear);
					$("#pictures_edit_text").val(data.text);
				}
			});
		}

		$("#pictures_edit").dialog(
		{
			autoOpen: false,
			closeText: "Schlie&szlig;en",
			minWidth: 500,
			modal: true,
			title: "Album bearbeiten",
			width: 800,
			buttons:
			{
				"OK": function ()
				{
					$("#pictures_edit_form")[0].submit();
				},
				"Abbrechen": function ()
				{
					$(this).dialog("close");
				}
			}
		});

		<?php
		if (Constants::$pagePath[1] == "album" and Constants::$pagePath[2])
		{
			?>
			$("#gallery > li > a").contextMenu("pictures_edit_contextmenu",
			{
				bindings:
				{
					pictures_edit_contextmenu_edittitle: function(trigger)
					{
						var element = $(trigger);
						var title = prompt("Gebe den Titel von dem Bild ein.", element.attr("caption"));
						if (title != null)
						{
							$.ajax(
							{
								type: "POST",
								data:
								{
									pictures_edittitle_albumId: <?php echo Constants::$pagePath[2];?>,
									pictures_edittitle_number: element.attr("number"),
									pictures_edittitle_title: title
								},
								url: "/pictures/setpicturetitle",
								error: function (jqXhr, textStatus, errorThrown)
								{
									noty(
									{
										type: "error",
										text: "Fehler beim Speichern des Titels!"
									});
								},
								success: function (data, status, jqXhr)
								{
									if (data == "ok")
									{
										element.attr("caption", title);
										noty(
										{
											type: "success",
											text: "Der Titel wurde ge\u00e4ndert."
										});
									}
									else
									{
										noty(
										{
											type: "error",
											text: "Fehler beim Speichern des Titels!"
										});
									}
								}
							});
						}
					},
					pictures_edit_contextmenu_setcover: function(trigger)
					{
						var element = $(trigger);
						if (confirm("Soll das Bild als Cover verwendet werden?"))
						{
							$.ajax(
							{
								type: "POST",
								data:
								{
									pictures_setcover_albumId: <?php echo Constants::$pagePath[2];?>,
									pictures_setcover_number: element.attr("number")
								},
								url: "/pictures/setalbumcover",
								error: function (jqXhr, textStatus, errorThrown)
								{
									noty(
									{
										type: "error",
										text: "Fehler beim Speichern des Albumcovers!"
									});
								},
								success: function (data, status, jqXhr)
								{
									if (data = "ok")
									{
										noty(
										{
											type: "success",
											text: "Das Albumcover wurde festgelegt."
										});
									}
									else
									{
										noty(
										{
											type: "error",
											text: "Fehler beim Speichern des Albumcovers!"
										});
									}
								}
							});
						}
					}
				}
			});
			<?php
		}
 		?>
	</script>
	<?php
}
?>