<h1>Fastnachtsfilme</h1>

<?php
if (Constants::$pagePath[2] and Constants::$pagePath[3])
{
	$userData = Constants::$accountManager->getUserData();
	
	if (Constants::$pagePath[2] == "cancel")
	{
		$query = Constants::$pdo->prepare("SELECT `movieId`, `buy` FROM `movieorders` WHERE `id` = :id AND `userId` = :userId");
		$query->execute(array
		(
			":id" => Constants::$pagePath[3],
			":userId" => Constants::$accountManager->getUserId()
		));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			
			$query = Constants::$pdo->prepare("DELETE FROM `movieorders` WHERE `id` = :id");
			$query->execute(array
			(
				":id" => Constants::$pagePath[3]
			));
			
			if (!$row->buy)
			{
				$query = Constants::$pdo->prepare("UPDATE `movies` SET `borrowed` = '0', `borrowedTo` = '' WHERE `id` = :id AND `borrowedTo` = :borrowedTo");
				$query->execute(array
				(
					":borrowedTo" => "userId:" . Constants::$accountManager->getUserId(),
					":id" => $row->movieId
				));
			}
			
			$query = Constants::$pdo->prepare("SELECT `movies`.`eventYear`, `movies`.`discs`, `movies`.`price`, `movietypes`.`title` AS `discType`, `moviecategories`.`title` AS `title` FROM `movies` LEFT JOIN `moviecategories` ON `moviecategories`.`id` = `movies`.`categoryId` LEFT JOIN `movietypes` ON `movietypes`.`id` = `movies`.`discTypeId` WHERE `movies`.`id` = :id");
			$query->execute(array
			(
				":id" => $row->movieId
			));
			$movieRow = $query->fetch();
			
			$replacements = array
			(
				"%USERNAME%" => $userData->username,
				"%FIRSTNAME%" => $userData->firstName,
				"%LASTNAME%" => $userData->lastName,
				"%TITLE%" => $movieRow->title,
				"%YEAR%" => $movieRow->eventYear,
				"%MEDIA%" => $movieRow->discs . " " . $movieRow->discType . ($movieRow->discs == 1 ? "" : "s"),
				"%ORDERTYPE%" => $row->buy ? "Kaufen" : "Ausleihen",
				"%PRICE%" => $row->buy ? (number_format($movieRow->price, 2, ",", ".") . " &euro;") : "Kostenlos",
			);
			
			$mail = new Mail(null, $replacements);
			
			$mail->newMessage("Deine Filmstornierung");
			$mail->send("movie-order-cancel", $userData->email);
			
			$mail->newMessage("Filmstornierung");
			$mail->send("webmaster-movie-order-cancel", WEBMASTER_EMAIL);
			
			echo "
				<div class='ok'>
					Deine Bestellung wurde storniert.<br />
					Du erh&auml;lst in K&uuml;rze eine Email zur Best&auml;tigung deiner Stornierung.
				</div>
			";
		}
		else
		{
			echo "<div class='error'>Die Bestellung wurde nicht gefunden!</div>";
		}
	}
	else
	{
		$query = Constants::$pdo->prepare("SELECT `movies`.`eventYear`, `movies`.`discs`, `movies`.`borrowed`, `movies`.`borrowedTo`, `movies`.`price`, `movietypes`.`title` AS `discType`, `moviecategories`.`title` AS `title` FROM `movies` LEFT JOIN `moviecategories` ON `moviecategories`.`id` = `movies`.`categoryId` LEFT JOIN `movietypes` ON `movietypes`.`id` = `movies`.`discTypeId` WHERE `movies`.`id` = :id");
		$query->execute(array
		(
			":id" => Constants::$pagePath[3]
		));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			
			if ($row->borrowed)
			{
				if ($row->borrowedTo == "userId:" . Constants::$accountManager->getUserId())
				{
					echo "<div class='error'>Dieser Film ist bereits von dir ausgeliehen!</div>";
				}
				else
				{
					echo "
						<div class='error'>
							Dieser Film ist derzeit ausgeliehen!<br />
							M&ouml;chtest du ihn stattdessen f&uuml;r " . number_format($row->price, 2, ",", ".") . " &euro; <a href='/internarea/movies/buy/" . Constants::$pagePath[3] . "'>kaufen</a>?
						</div>
					";
				}
			}
			else
			{
				$query = Constants::$pdo->prepare("INSERT INTO `movieorders` (`date`, `movieId`, `buy`, `userId`) VALUES(NOW(), :movieId, :buy, :userId)");
				$query->execute(array
				(
					":movieId" => Constants::$pagePath[3],
					":buy" => Constants::$pagePath[2] == "buy" ? 1 : 0,
					":userId" => Constants::$accountManager->getUserId()
				));
				$orderId = Constants::$pdo->lastInsertId();
				
				if (Constants::$pagePath[2] != "buy")
				{
					$query = Constants::$pdo->prepare("UPDATE `movies` SET `borrowed` = '1', `borrowedTo` = :borrowedTo WHERE `id` = :id");
					$query->execute(array
					(
						":borrowedTo" => "userId:" . Constants::$accountManager->getUserId(),
						":id" => Constants::$pagePath[3]
					));
				}
				
				$replacements = array
				(
					"%USERNAME%" => $userData->username,
					"%FIRSTNAME%" => $userData->firstName,
					"%LASTNAME%" => $userData->lastName,
					"%TITLE%" => $row->title,
					"%YEAR%" => $row->eventYear,
					"%MEDIA%" => $row->discs . " " . $row->discType . ($row->discs == 1 ? "" : "s"),
					"%ORDERTYPE%" => Constants::$pagePath[2] == "buy" ? "Kaufen" : "Ausleihen",
					"%PRICE%" => Constants::$pagePath[2] == "buy" ? (number_format($row->price, 2, ",", ".") . " &euro;") : "Kostenlos",
					"%CANCELURL%" => BASE_URL . "/internarea/movies/cancel/" . $orderId
				);
				
				$mail = new Mail(null, $replacements);
				
				$mail->newMessage("Deine Filmbestellung");
				$mail->send($userData->email, "movie-order");
				
				$mail->newMessage("Filmbestellung");
				$mail->send(WEBMASTER_EMAIL, "webmaster-movie-order");
				
				echo "
					<div class='ok'>
						Deine Bestellung wurde eingetragen und wird in den n&auml;chsten Stunden oder Tagen bearbeitet.<br />
						Du erh&auml;lst in K&uuml;rze eine Email zur Best&auml;tigung deiner Bestellung.
					</div>
				";
			}
		}
		else
		{
			echo "<div class='error'>Der Film wurde nicht gefunden!</div>";
		}
	}
}
?>

<table id="movies_table" class="table">
	<thead>
		<tr>
			<th></th>
			<th>Titel</th>
			<th>Jahr</th>
			<th>Kaufpreis</th>
			<th>Ausleihen m&ouml;glich</th>
			<th>Kommentar</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<?php
		$query = Constants::$pdo->query("SELECT `movies`.`eventYear`, `movies`.`categoryId`, `moviecategories`.`title`, `movies`.`id`, `movies`.`discs`, `movies`.`price`, `movies`.`borrowed`, `movies`.`comment`, `movietypes`.`name` AS `discType`, `movietypes`.`title` AS `discTypeTitle` FROM `movies` LEFT JOIN `moviecategories` ON `moviecategories`.`id` = `movies`.`categoryId` LEFT JOIN `movietypes` ON `movietypes`.`id` = `movies`.`discTypeId`");
		while ($row = $query->fetch())
		{
			$coverImage = "/files/images/movies/covers/" . $row->eventYear . "-" . $row->categoryId . ".jpg";
			if (!file_exists(ROOT_PATH . $coverImage))
			{
				$coverImage = "/files/images/movies/covers/default.jpg";
			}
			
			echo "
				<tr class='movies_item' movieid='" . $row->id . "'>
					<td number='" . $row->discType . "' title='" . $row->discs . " " . $row->discTypeTitle . ($row->discs == 1 ? "" : "s") . "' class='movies_item'>
						<img class='movies_item_cover' src='" . $coverImage . "'/>
			";
			if ($row->discs == 1)
			{
				echo "<img class='movies_item_disc' src='/files/images/movies/types/" . $row->discType . ".png'/>";
			}
			else
			{
				echo "
					<img class='movies_item_disc movies_item_disc1' src='/files/images/movies/types/" . $row->discType . ".png'/>
					<img class='movies_item_disc movies_item_disc2' src='/files/images/movies/types/" . $row->discType . ".png'/>
				";
			}
			echo "
					</td>
					<td number='" . $row->categoryId . "'>" . $row->title . "</td>
					<td number='" . $row->eventYear . "'>" . $row->eventYear . "</td>
					<td number='" . $row->price . "'>" . number_format($row->price, 2, ",", ".") . " &euro;</td>
					<td number='" . $row->borrowed . "' class='" . ($row->borrowed ? "movies_unavailable" : "movies_available") . "' title='" . ($row->borrowed ? "Derzeit ausgeliehen" : "Verf&uuml;gbar") . "'></td>
					<td>" . $row->comment . "</td>
					<td>
						<button type='button' onclick='movies_confirm(this, true);'>Kaufen (" . number_format($row->price, 2, ",", ".") . " &euro;)</button>
						<button type='button' onclick='movies_confirm(this, false);' " . ($row->borrowed ? "disabled" : "") . ">Ausleihen (Kostenlos)</button>
					</td>
				</tr>
			";
		}
		?>
	</tbody>
</table>

<div id="movies_confirm" title="Film bestellen">
	<p id="movies_confirm_text"></p>
	<table>
		<tbody id="movies_confirm_table"></tbody>
	</table>
</div>

<script type="text/javascript">
	$("#movies_table").tablesorter(
	{
		headers :
		{
			0 :
			{
				sorter : "number-attribute"
			},
			1 :
			{
				sorter : "number-attribute"
			},
			2 :
			{
				sorter : "number-attribute"
			},
			3 :
			{
				sorter : "number-attribute"
			},
			4 :
			{
				sorter : "number-attribute"
			}
		},
		sortList : [[2, 0], [1, 0], [0, 0]]
	});
	
	function movies_confirm(button, buy)
	{
		var row = button.parentNode.parentNode;
		var cells = row.getElementsByTagName("td");
		
		var tableContent = "";
		tableContent += "<tr>";
		tableContent += "<td>Titel:</td><td>" + cells[1].innerHTML + " (" + cells[2].innerHTML + ")</td>";
		tableContent += "</tr>";
		tableContent += "<tr>";
		tableContent += "<td>Medium:</td><td>" + cells[0].title + "</td>";
		tableContent += "</tr>";
		if (buy)
		{
			tableContent += "<tr>";
			tableContent += "<td>Preis:</td><td>" + cells[3].innerHTML + "</td>";
			tableContent += "</tr>";
		}
		
		$("#movies_confirm_table").html(tableContent);
		
		var text = "";
		if (buy)
		{
			text = "M&ouml;chtest du den ausgew&auml;hlten Film kaufen?";
		}
		else
		{
			text = "M&ouml;chtest du den ausgew&auml;hlten Film ausleihen?";
		}
		
		$("#movies_confirm_text").html(text);
		
		$("#movies_confirm").dialog(
		{
			resizable : false,
			modal : true,
			width : "auto",
			buttons :
			{
				"OK" : function()
				{
					document.location.href = "/internarea/movies/" + (buy ? "buy" : "borrow") + "/" + row.getAttribute("movieid");
				},
				"Abbrechen" : function()
				{
					$(this).dialog("close");
				}
			}
		});
	}
</script>