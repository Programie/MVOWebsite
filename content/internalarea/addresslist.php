<?php
$row = new StdClass;
$row->name = "all";
$row->title = "Alle";
$row->active = true;
$groups = array($row->name => $row);

$activeGroup = "all";

$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups`");
while ($row = $query->fetch())
{
	if ($row->name == Constants::$pagePath[2])
	{
		$row->active = true;
		$groups["all"]->active = false;
		$activeGroup = $row->name;
	}
	$groups[$row->name] = $row;
}

$title = "Adressenliste";

if ($activeGroup != "all")
{
	$title .= " - " . $groups[$activeGroup]->title;
}
echo "<h1>" . $title . "</h1>";

if (isset($_POST["addresslist_sendmessage_confirmed"]))
{
	$showError = true;
	if ($_POST["addresslist_sendmessage_confirmed"])
	{
		$userData = Constants::$accountManager->getUserData();
		if ($_POST["addresslist_sendmessage_sendtoken"] == $userData->sendToken)
		{
			$recipients = explode(",", $_POST["addresslist_sendmessage_recipients"]);
			if (!empty($recipients))
			{
				$mailRecipients = array();
				$query = Constants::$pdo->prepare("SELECT `email`, `firstName`, `lastName` FROM `users` WHERE `id` = :id");
				foreach ($recipients as $index => $recipientUserId)
				{
					$query->execute(array
					(
						":id" => $recipientUserId
					));
					$row = $query->fetch();
					
					if ($row->email)
					{
						$mailRecipients[$row->email] = $row->firstName . " " . $row->lastName;
					}
				}
				
				$ccMail = null;
				if ($_POST["addresslist_sendmessage_sendcopy"])
				{
					$ccMail = array($userData->email => $userData->firstName . " " . $userData->lastName);
				}
				
				$mail = new Mail("Nachricht vom Internen Bereich");
				$mail->setTemplate("addresslist-sendmessage");
				$mail->addReplacement("CONTENT", formatText($_POST["addresslist_sendmessage_text"]));
				$mail->setTo($mailRecipients);
				$mail->setCc($ccMail);
				$mail->setReplyTo(array($userData->email => $userData->firstName . " " . $userData->lastName));
				if ($mail->send())
				{
					echo "<div class='ok'>Die Nachricht wurde erfolgreich an <b>" . count($mailRecipients) . " Empf&auml;nger</b> gesendet.</div>";
					$showError = false;
				}
			}
		}
		else
		{
			echo "<div class='error'>Es wurde versucht dieselbe Email erneut zu versenden!</div>";
			$showError = false;
		}
	}
	if ($showError)
	{
		echo "<div class='error'>Beim Senden der Nachricht ist ein Fehler aufgetreten!</div>";
	}
}
?>

<fieldset id="addresslist_groups">
	<legend>Gruppen</legend>
	<?php
	foreach ($groups as $name => $row)
	{
		$addStyle = "";
		if ($row->active)
		{
			$addStyle = "style='font-weight: bold;'";
		}
		echo "<a href='/internalarea/addresslist/" . $name . "' " . $addStyle . "><button type='button'>" . $row->title . "</button></a>";
	}
	?>
</fieldset>

<table id="addresslist_table" class="table">
	<thead>
		<tr>
			<th></th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Email</th>
			<th>Telefon (Privat)</th>
			<th>Telefon (Gesch&auml;ftlich)</th>
			<th>Mobil</th>
			<th>Fax</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$permissionCheckQuery = Constants::$pdo->prepare("SELECT `id` FROM `permissions` WHERE `userId` = :userId AND `permission` = :permission");
		$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName`, `email`, `phonePrivate`, `phoneWork`, `phoneMobile`, `fax` FROM `users`");
		while ($row = $query->fetch())
		{
			if ($activeGroup == "all")
			{
				$show = true;
			}
			else
			{
				$permissionCheckQuery->execute(array
				(
					":userId" => $row->id,
					":permission" => "groups." . $activeGroup
				));
				$show = $permissionCheckQuery->rowCount();
			}
			if ($show)
			{
				echo "
					<tr userid='" . $row->id . "'>
						<td><input type='checkbox'/></td>
						<td>" . $row->firstName . "</td>
						<td>" . $row->lastName . "</td>
						<td>" . $row->email . "</td>
						<td>" . $row->phonePrivate . "</td>
						<td>" . $row->phoneWork . "</td>
						<td>" . $row->phoneMobile . "</td>
						<td>" . $row->fax . "</td>
					</tr>
				";
			}
		}
		?>
	</tbody>
</table>

<fieldset id="addresslist_sendmessage">
	<legend>Nachricht senden</legend>
	
	<form id="addresslist_sendmessage_form" action="/internalarea/addresslist" method="post" onsubmit="addresslist_sendMessageConfirm(); return false;">
		<textarea id="addresslist_sendmessage_text" name="addresslist_sendmessage_text" rows="15" cols="15"></textarea>
		<input type="hidden" id="addresslist_sendmessage_sendcopy" name="addresslist_sendmessage_sendcopy"/>
		<input type="hidden" id="addresslist_sendmessage_confirmed" name="addresslist_sendmessage_confirmed"/>
		<input type="hidden" id="addresslist_sendmessage_recipients" name="addresslist_sendmessage_recipients"/>
		<input type="hidden" name="addresslist_sendmessage_sendtoken" value="<?php echo Constants::$accountManager->getSendToken();?>"/>
		<input type="submit" value="Senden"/>
	</form>
</fieldset>

<div id="addresslist_sendmessage_confirm" title="Nachricht senden">
	<p id="addresslist_sendmessage_confirm_text"></p>
	<ul id="addresslist_sendmessage_confirm_recipients"></ul>
	<input id="addresslist_sendmessage_confirm_sendcopy" type="checkbox"/><label for="addresslist_sendmessage_confirm_sendcopy">Eine Kopie an mich senden</label>
</div>

<script type="text/javascript">
	$("#addresslist_table").tablesorter(
	{
		sortList : [[2, 0], [1, 0]]
	});
	
	$("#addresslist_sendmessage_confirm").dialog(
	{
		resizable : false,
		modal : true,
		width : "auto",
		maxHeight : 500,
		autoOpen : false,
		buttons :
		{
			"Senden" : function()
			{
				document.getElementById("addresslist_sendmessage_sendcopy").value = document.getElementById("addresslist_sendmessage_confirm_sendcopy").checked ? "1" : "0";
				document.getElementById("addresslist_sendmessage_confirmed").value = true;
				document.getElementById("addresslist_sendmessage_form").submit();
			},
			"Abbrechen" : function()
			{
				$(this).dialog("close");
			}
		}
	});
	
	$("#addresslist_table tbody tr").click(function(event)
	{
		if (event.target.type != "checkbox")
		{
			$(":checkbox", this).trigger("click");
		}
	});
	
	function addresslist_sendMessageConfirm()
	{
		var recipients = [];
		
		$("#addresslist_sendmessage_confirm_recipients").html("");
		
		$("#addresslist_table tbody:first tr").each(function()
		{
			var cells = $(this).find("td");
			if (cells.eq(0).find("input:checkbox").is(":checked"))
			{
				recipients.push($(this).attr("userid"));
				$("#addresslist_sendmessage_confirm_recipients").append("<li>" + cells.eq(1).html() + " " + cells.eq(2).html() + "</li>");
			}
		});
		
		if (recipients.length)
		{
			document.getElementById("addresslist_sendmessage_recipients").value = recipients.join(",");
			$("#addresslist_sendmessage_confirm_text").html("Soll die Nachricht jetzt an die folgenden " + recipients.length + " Emp&auml;nger gesendet werden?");
			$("#addresslist_sendmessage_confirm").dialog("open");
		}
		else
		{
			alert(unescape("Kein Emp%E4nger ausgew%E4hlt!"));
		}
	}
</script>