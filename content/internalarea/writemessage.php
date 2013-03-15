<h1>Nachricht verfassen</h1>

<?php
if (isset($_POST["writemessage_confirmed"]))
{
	$showError = true;
	if ($_POST["writemessage_confirmed"] and $_POST["writemessage_text"])
	{
		$userData = Constants::$accountManager->getUserData();
		if ($_POST["writemessage_sendtoken"] == $userData->sendToken)
		{
			$send = true;
			
			$groups = array();
			$mailRecipients = array();
			
			$permissionQuery = Constants::$pdo->prepare("SELECT `userId` FROM `permissions` WHERE `permission` = :permission");
			$userQuery = Constants::$pdo->prepare("SELECT `email`, `firstName`, `lastName` FROM `users` WHERE `id` = :id");
			
			foreach ($_POST as $field => $value)
			{
				if (substr($field, 0, 19) == "writemessage_group_" and $value)
				{
					$group = substr($field, 19);
					
					$groups[] = $group;
					
					$permissionQuery->execute(array
					(
						":permission" => "groups." . $group
					));
					while ($permissionRow = $permissionQuery->fetch())
					{
						$userQuery->execute(array
						(
							":id" => $permissionRow->userId
						));
						$userRow = $userQuery->fetch();
						$mailRecipients[$userRow->email] = $userRow->firstName . " " . $userRow->lastName;
					}
				}
			}
			
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
					echo "<div class='error'>Beim Hochladen der Datei <b>" . $fileData["name"] . "</b> ist ein Fehler aufgetreten!</div>";
					$showError = false;
					$send = false;
					break;
				}
			}
			
			if (!empty($mailRecipients) and $send)
			{
				$ccMail = null;
				if ($_POST["writemessage_sendcopy"])
				{
					$ccMail = array($userData->email => $userData->firstName . " " . $userData->lastName);
				}
				
				$text = $_POST["writemessage_text"];
				
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
				
				$query = Constants::$pdo->prepare("INSERT INTO `messages` (`date`, `targetGroups`, `userId`, `text`, `attachedFiles`) VALUES(NOW(), :targetGroups, :userId, :text, :attachedFiles)");
				$query->execute(array
				(
					":targetGroups" => implode("\n", $groups),
					":userId" => Constants::$accountManager->getUserId(),
					":text" => $text,
					":attachedFiles" => implode("\n", $attachedFiles)
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
				
				$replacements = array
				(
					"FIRSTNAME" => $userData->firstName,
					"LASTNAME" => $userData->lastName,
					"CONTENT" => formatText($text),
					"ATTACHMENTS" => implode("\n", $attachmentsText),
					"URL" => BASE_URL . "/internalarea/messages/" . $messageId
				);
				$mail = new Mail("Neue Nachricht im Internen Bereich", $replacements);
				$mail->setTemplate("writemessage");
				$mail->setTo($mailRecipients);
				$mail->setCc($ccMail);
				$mail->setReplyTo(array($userData->email => $userData->firstName . " " . $userData->lastName));
				if ($mail->send())
				{
					echo "
						<div class='ok'>
							<p>Die Nachricht wurde erfolgreich an <b>" . count($mailRecipients) . " Empf&auml;nger</b> gesendet.</p>
							" . implode("\n", $attachmentsText) . "
						</div>
					";
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

<form id="writemessage_form" action="/internalarea/writemessage" method="post" enctype="multipart/form-data" onsubmit="writeMessage_confirm(); return false;">
	<fieldset id="writemessage_groups">
		<legend>Gruppen</legend>
		<?php
		$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups`");
		while ($row = $query->fetch())
		{
			echo "<input type='checkbox' id='writemessage_group_" . $row->name . "' name='writemessage_group_" . $row->name . "' value='1'/><label for='writemessage_group_" . $row->name . "'>" . $row->title . "</label>";
		}
		?>
	</fieldset>
	
	<textarea id="writemessage_text" name="writemessage_text" rows="15" cols="15"></textarea>
	
	<fieldset id="writemessage_attachments">
		<legend>Anh&auml;nge</legend>
	</fieldset>
	
	<input type="hidden" id="writemessage_sendcopy" name="writemessage_sendcopy"/>
	<input type="hidden" id="writemessage_confirmed" name="writemessage_confirmed"/>
	<input type="hidden" name="writemessage_sendtoken" value="<?php echo Constants::$accountManager->getSendToken();?>"/>
	
	<input type="submit" value="Senden"/>
</form>

<div id="writemessage_confirm" title="Nachricht senden">
	<p id="writemessage_confirm_text1"></p>
	<ul id="writemessage_confirm_groups"></ul>
	<p id="writemessage_confirm_text2"><b>Anh&auml;nge:</b></p>
	<ul id="writemessage_confirm_attachments"></ul>
	<input id="writemessage_confirm_sendcopy" type="checkbox"/><label for="writemessage_confirm_sendcopy">Eine Kopie an mich senden</label>
</div>

<script type="text/javascript">
	writemessage_attachments_file = 0;
	writeMessage_addAttachmentFile();
	
	$("#writemessage_confirm").dialog(
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
				document.getElementById("writemessage_sendcopy").value = document.getElementById("writemessage_confirm_sendcopy").checked ? "1" : "0";
				document.getElementById("writemessage_confirmed").value = true;
				document.getElementById("writemessage_form").submit();
			},
			"Abbrechen" : function()
			{
				$(this).dialog("close");
			}
		}
	});
	
	function writeMessage_addAttachmentFile()
	{
		writemessage_attachments_file++;
		$("#writemessage_attachments").append("<input type='file' class='writemessage_attachments_file' id='writemessage_attachments_file_" + writemessage_attachments_file+ "' name='writemessage_attachments_file_" + writemessage_attachments_file+ "' onchange='writeMessage_checkAttachmentFields();'/>");
	}
	
	function writeMessage_checkAttachmentFields()
	{
		var addNew = true;
		$(".writemessage_attachments_file").each(function()
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
			writeMessage_addAttachmentFile();
		}
	}
	
	function writeMessage_confirm()
	{
		var groups = 0;
		
		$("#writemessage_confirm_groups").html("");
		
		$("#writemessage_groups input:checkbox").each(function()
		{
			if ($(this).is(":checked"))
			{
				groups++;
				$("#writemessage_confirm_groups").append("<li>" + $("label[for='" + $(this).attr("id") + "']").text() + "</li>");
			}
		});
		
		if (groups)
		{
			if (document.getElementById("writemessage_text").value)
			{
				$("#writemessage_confirm_text1").html("Soll die Nachricht jetzt an die folgenden " + groups + " Gruppen gesendet werden?");
				
				var attachments = 0;
				$("#writemessage_confirm_attachments").html("");
				$(".writemessage_attachments_file").each(function()
				{
					if ($(this)[0].files.length)
					{
						attachments++;
						$("#writemessage_confirm_attachments").append("<li>" + $(this)[0].files[0].name + "</li>");
					}
				});
				attachments ? $("#writemessage_confirm_text2").show() : $("#writemessage_confirm_text2").hide();
				
				$("#writemessage_confirm").dialog("open");
			}
			else
			{
				alert("Kein Text eingegeben!");
			}
		}
		else
		{
			alert(unescape("Keine Gruppe ausgew%E4hlt!"));
		}
	}
</script>