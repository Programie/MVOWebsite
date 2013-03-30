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
	$error = "Beim Senden der Nachricht ist ein Fehler aufgetreten!";
	if ($_POST["addresslist_sendmessage_confirmed"])
	{
		$userData = Constants::$accountManager->getUserData();
		if ($_POST["addresslist_sendmessage_sendtoken"] == $userData->sendToken)
		{
			$targetUsers = array();
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
						$targetUsers[] = "uid:" . $recipientUserId;
					}
				}
				
				$send = true;
				
				$uploadedFiles = array();
				foreach ($_FILES as $fileData)
				{
					$uploadError = false;
					if ($fileData["error"] == UPLOAD_ERR_OK)
					{
						$fileName = md5_file($fileData["tmp_name"]);
						if (move_uploaded_file($fileData["tmp_name"], UPLOAD_PATH . "/" . $fileName))
						{
							$uploadedFiles[$fileName] = $fileData["name"];
						}
						else
						{
							$uploadError = true;
						}
					}
					else
					{
						if ($fileData["error"] != UPLOAD_ERR_NO_FILE)// One file field is always empty
						{
							$uploadError = true;
						}
					}
					if ($uploadError)
					{
						$error = "Beim Hochladen der Datei <b>" . $fileData["name"] . "</b> ist ein Fehler aufgetreten!";
						$send = false;
						break;
					}
				}
				
				if ($send)
				{
					$attachedFiles = array();
					$addFileQuery = Constants::$pdo->prepare("INSERT INTO `uploads` (`name`, `title`) VALUES(:name, :title)");
					foreach ($uploadedFiles as $name => $title)
					{
						$addFileQuery->execute(array
						(
							":name" => $name,
							":title" => $title
						));
						$attachedFiles[$name] = Constants::$pdo->lastInsertId();
					}
					
					$text = $_POST["addresslist_sendmessage_text"];
					
					$query = Constants::$pdo->prepare("INSERT INTO `messages` (`date`, `targetGroups`, `userId`, `text`, `attachedFiles`) VALUES(NOW(), :targetGroups, :userId, :text, :attachedFiles)");
					$query->execute(array
					(
						":targetGroups" => implode(",", $targetUsers),
						":userId" => Constants::$accountManager->getUserId(),
						":text" => $text,
						":attachedFiles" => implode(",", $attachedFiles)
					));
					$messageId = Constants::$pdo->lastInsertId();
					
					$attachmentsText = array();
					if (!empty($uploadedFiles))
					{
						$attachmentsText[] = "<p><b>Anh&auml;nge:</b></p>";
						$attachmentsText[] = "<ul>";
						foreach ($uploadedFiles as $name => $title)
						{
							$attachmentsText[] = "<li><a href='" . BASE_URL . "/uploads/" . $attachedFiles[$name] . "/" . $name . "'>" . $title . "</a></li>";
						}
						$attachmentsText[] = "</ul>";
					}
					
					$ccMail = null;
					if ($_POST["addresslist_sendmessage_sendcopy"])
					{
						$ccMail = array($userData->email => $userData->firstName . " " . $userData->lastName);
					}
					
					$replacements = array
					(
						"ATTACHMENTS" => implode("\n", $attachmentsText),
						"CONTENT" => formatText($text),
						"FIRSTNAME" => $userData->firstName,
						"LASTNAME" => $userData->lastName,
						"MESSAGEID" => $messageId
					);
					$mail = new Mail("Nachricht vom Internen Bereich", $replacements);
					$mail->setTemplate("writemessage");
					$mail->setTo($mailRecipients);
					$mail->setCc($ccMail);
					$mail->setReplyTo(array($userData->email => $userData->firstName . " " . $userData->lastName));
					if ($mail->send())
					{
						$error = "";
						echo "<div class='ok'>Die Nachricht wurde erfolgreich an <b>" . count($mailRecipients) . " Empf&auml;nger</b> gesendet.</div>";
					}
				}
			}
		}
		else
		{
			$error = "Es wurde versucht dieselbe Email erneut zu versenden!";
		}
	}
	if ($error)
	{
		echo "<div class='error'>" . $error . "</div>";
	}
}
?>

<fieldset id="addresslist_groups">
	<legend>Gruppen</legend>
	<?php
	foreach ($groups as $name => $row)
	{
		$buttonStyle = "";
		if ($row->active)
		{
			$buttonStyle = "style='font-weight: bold;'";
		}
		echo "<a href='/internalarea/addresslist/" . $name . "'><button type='button' " . $buttonStyle . ">" . $row->title . "</button></a>";
	}
	?>
</fieldset>

<table id="addresslist_table" class="table {sortlist: [[2,0],[1,0]]}">
	<thead>
		<tr>
			<th class="no-print"></th>
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
		$query = Constants::$pdo->query("SELECT `id`, `firstName`, `lastName`, `email`, `phonePrivate1`, `phonePrivate2`, `phoneWork`, `phoneMobile`, `fax` FROM `users`");
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
				$phonePrivate = array();
				if ($row->phonePrivate1)
				{
					$phonePrivate[] = $row->phonePrivate1;
				}
				if ($row->phonePrivate2)
				{
					$phonePrivate[] = $row->phonePrivate2;
				}
				echo "
					<tr userid='" . $row->id . "'>
						<td class='no-print'><input type='checkbox'/></td>
						<td>" . $row->firstName . "</td>
						<td>" . $row->lastName . "</td>
						<td>" . $row->email . "</td>
						<td>" . implode("<br />", $phonePrivate) . "</td>
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
	
	<form id="addresslist_sendmessage_form" action="/internalarea/addresslist" method="post" enctype="multipart/form-data" onsubmit="addresslist_sendMessageConfirm(); return false;">
		<textarea id="addresslist_sendmessage_text" name="addresslist_sendmessage_text" rows="15" cols="15"></textarea>
		
		<fieldset id="addresslist_sendmessage_attachments">
			<legend>Anh&auml;nge</legend>
		</fieldset>
		
		<input type="hidden" id="addresslist_sendmessage_sendcopy" name="addresslist_sendmessage_sendcopy"/>
		<input type="hidden" id="addresslist_sendmessage_confirmed" name="addresslist_sendmessage_confirmed"/>
		<input type="hidden" id="addresslist_sendmessage_recipients" name="addresslist_sendmessage_recipients"/>
		<input type="hidden" name="addresslist_sendmessage_sendtoken" value="<?php echo Constants::$accountManager->getSendToken();?>"/>
		<input type="submit" value="Senden"/>
	</form>
</fieldset>

<div id="addresslist_sendmessage_confirm" title="Nachricht senden">
	<p id="addresslist_sendmessage_confirm_text1"></p>
	<ul id="addresslist_sendmessage_confirm_recipients"></ul>
	<p id="addresslist_sendmessage_confirm_text2"><b>Anh&auml;nge:</b></p>
	<ul id="addresslist_sendmessage_confirm_attachments"></ul>
	<input id="addresslist_sendmessage_confirm_sendcopy" type="checkbox"/><label for="addresslist_sendmessage_confirm_sendcopy">Eine Kopie an mich senden</label>
</div>

<script type="text/javascript">
	addresslist_sendmessage_attachments_file = 0;
	addresslist_sendMessageAddAttachmentFile();
	
	$("#addresslist_sendmessage_confirm").dialog(
	{
		closeText : "Schlie&szlig;en",
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
	
	function addresslist_sendMessageAddAttachmentFile()
	{
		addresslist_sendmessage_attachments_file++;
		$("#addresslist_sendmessage_attachments").append("<input type='file' class='addresslist_sendmessage_attachments_file' id='addresslist_sendmessage_attachments_file_" + addresslist_sendmessage_attachments_file + "' name='addresslist_sendmessage_attachments_file_" + addresslist_sendmessage_attachments_file + "' onchange='addresslist_sendMessageCheckAttachmentFields();'/>");
	}
	
	function addresslist_sendMessageCheckAttachmentFields()
	{
		var addNew = true;
		$(".addresslist_sendmessage_attachments_file").each(function()
		{
			if (!$(this)[0].files.length)
			{
				if (!addNew)// Another field is already empty -> Remove this one
				{
					$(this).remove();
				}
				addNew = false;
			}
		});
		if (addNew)
		{
			addresslist_sendMessageAddAttachmentFile();
		}
	}
	
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
			$("#addresslist_sendmessage_confirm_text1").html("Soll die Nachricht jetzt an die folgenden " + recipients.length + " Emp&auml;nger gesendet werden?");
			
			var attachments = 0;
			$("#addresslist_sendmessage_confirm_attachments").html("");
			$(".addresslist_sendmessage_attachments_file").each(function()
			{
				if ($(this)[0].files.length)
				{
					attachments++;
					$("#addresslist_sendmessage_confirm_attachments").append("<li>" + $(this)[0].files[0].name + "</li>");
				}
			});
			attachments ? $("#addresslist_sendmessage_confirm_text2").show() : $("#addresslist_sendmessage_confirm_text2").hide();
			
			$("#addresslist_sendmessage_confirm").dialog("open");
		}
		else
		{
			alert(unescape("Kein Emp%E4nger ausgew%E4hlt!"));
		}
	}
</script>