<?php
class MessageManager
{
	public function addEditOptions()
	{
		if (Constants::$accountManager->hasPermission("messages.delete"))
		{
			echo "
				<div id='messages_hide'>
					<p>Soll die ausgew&auml;hlte Nachricht wirklich ausgeblendet werden?</p>
					
					<div id='messages_hide_info'></div>
					
					<p><b>Hinweis:</b> Die Nachricht kann nur &uuml;ber die Datenbank wiederhergestellt werden!</p>
					
					<form id='messages_hide_form' method='post' onsubmit='return false'>
						<input type='hidden' id='messages_hide_sendtoken' name='messages_hide_sendtoken' value='" . Constants::$accountManager->getSendToken() . "'/>
						<input type='hidden' id='messages_hide_id' name='messages_hide_id'/>
					</form>
				</div>
				
				<div id='messages_edit_contextmenu'>
					<ul>
						<li id='messages_edit_contextmenu_hide'><img src='/files/images/contextmenu/trash.png'/> Ausblenden</li>
					</ul>
				</div>
				
				<script type='text/javascript'>
					$('#messages_hide').dialog(
					{
						autoOpen : false,
						closeText : 'Schlie&szlig;en',
						modal : true,
						resizable : false,
						title : 'Nachricht ausblenden',
						width : 'auto',
						buttons :
						{
							'OK' : function()
							{
								$('#messages_hide_form')[0].submit();
							},
							'Abbrechen' : function()
							{
								$(this).dialog('close');
							}
						}
					});
					
					$('.messages_container').contextMenu('messages_edit_contextmenu',
					{
						bindings :
						{
							messages_edit_contextmenu_hide : function(trigger)
							{
								$('#messages_hide_info').html($(trigger).find('.messages_header_container').html());
								$('#messages_hide_id').val($(trigger).attr('msgid'));
								$('#messages_hide').dialog('open');
							}
						}
					});
				</script>
			";
		}
	}
	
	public function processEdit()
	{
		if (Constants::$accountManager->hasPermission("messages.delete"))
		{
			if ($_POST["messages_hide_id"])
			{
				$userData = Constants::$accountManager->getUserData();
				if ($_POST["messages_hide_sendtoken"] == $userData->sendToken)
				{
					$query = Constants::$pdo->prepare("UPDATE `messages` SET `enabled` = '0' WHERE `id` = :id");
					$query->execute(array
					(
						":id" => $_POST["messages_hide_id"]
					));
					echo "<div class='ok'>Die &Auml;nderungen wurden erfolgreich gespeichert.</div>";
				}
				else
				{
					echo "<div class='error'>Es wurde versucht, die &Auml;nderungen erneut zu &uuml;bernehmen!</div>";
				}
			}
		}
	}
	
	public function showMessage($id)
	{
		$uploadedFileQuery = Constants::$pdo->prepare("SELECT `name`, `title` FROM `uploads` WHERE `id` = :id");
		$userGroupsQuery = Constants::$pdo->prepare("SELECT `title` FROM `usergroups` WHERE `name` = :name");
		$userQuery = Constants::$pdo->prepare("SELECT `id`, `firstName`, `lastName` FROM `users` WHERE `id` = :id");
		
		$sql = array();
		
		$sql[] = "SELECT `messages`.`id`, `messages`.`date`, `messages`.`validTill`, `messages`.`targetGroups`, `messages`.`text`, `messages`.`attachedFiles`, `users`.`id` AS `userId`, `users`.`firstName`, `users`.`lastName`, `users`.`email` FROM `messages`";
		$sql[] = "LEFT JOIN `users` ON `users`.`id` = `messages`.`userId`";
		$sql[] = "WHERE `enabled`";
		if ($id == null or $id == -1)
		{
			$sql[] = "AND (`validTill` IS NULL OR `validTill` >= CURDATE()) ORDER BY `messages`.`id` DESC";
			$query = Constants::$pdo->query(implode(" ", $sql));
		}
		else
		{
			$sql[] = "AND `messages`.`id` = :id";
			$query = Constants::$pdo->prepare(implode(" ", $sql));
			$query->execute(array
			(
				":id" => $id
			));
		}
		
		$messageCount = 0;
		$expired = false;
		
		while ($row = $query->fetch())
		{
			if ($id > 0 and strtotime($row->validTill . " 23:59:59") < time())
			{
				$expired = true;
				break;
			}
			
			$targetGroups = explode(",", $row->targetGroups);
			$attachedFiles = explode(",", $row->attachedFiles);
			
			$allowed = false;
			
			if ($row->userId == Constants::$accountManager->getUserId())
			{
				$allowed = true;
			}
			
			if (!$allowed)
			{
				foreach ($targetGroups as $groupName)
				{
					if (substr($groupName, 0, 4) == "uid:")
					{
						if (substr($groupName, 4) == Constants::$accountManager->getUserId())
						{
							$allowed = true;
							break;
						}
					}
				}
			}
			
			if (!$allowed and Constants::$accountManager->hasPermissionInArray($targetGroups, "messages.view"))
			{
				$allowed = true;
			}
			
			if (!$allowed)
			{
				continue;
			}
			
			$targets = array();
			foreach ($targetGroups as $groupName)
			{
				if (substr($groupName, 0, 4) == "uid:")
				{
					$userQuery->execute(array
					(
						":id" => substr($groupName, 4)
					));
					$userRow = $userQuery->fetch();
					if ($userRow->id)
					{
						$targets[] = escapeText($userRow->firstName) . " " . escapeText($userRow->lastName);
					}
				}
				else
				{
					$userGroupsQuery->execute(array
					(
						":name" => $groupName
					));
					$userGroupsRow = $userGroupsQuery->fetch();
					$targets[] = $userGroupsRow->title ? escapeText($userGroupsRow->title) : $groupName;
				}
			}
			
			echo "<div class='messages_container' msgid='" . $row->id . "'>";
			if ($id == null)// Only show link for single message in multi message view
			{
				echo "<a href='/internalarea/messages/" . $row->id . "' class='messages_header' title='Klicken um nur diese Nachricht anzuzeigen'>";
			}
			$profilePicturesPath = "/files/profilepictures";
			$avatarFile = $profilePicturesPath . "/" . $row->userId . ".jpg";
			if (!file_exists(ROOT_PATH . $avatarFile))
			{
				$avatarFile = $profilePicturesPath . "/default.png";
			}
			echo "
				<div class='messages_header'>
					<img class='messages_header_avatar' src='" . $avatarFile . "'/>
					<div class='messages_header_container'>
						<div><b>Erstellt von:</b> " . escapeText($row->firstName) . " " . escapeText($row->lastName) . " [" . escapeText($row->email) . "]</div>
						<div><b>Zeit:</b> " . date("d.m.Y H:i:s", strtotime($row->date)) . "</div>
			";
			if ($row->validTill)
			{
				echo "<div><b>G&uuml;ltig bis:</b> " . date("d.m.Y", strtotime($row->validTill)) . "</div>";
			}
			echo "
						<div><b>Gesendet an:</b> " . implode(", ", $targets) . "</div>
					</div>
				</div>
			";
			if ($id == null)// Only show link for single message in multi message view
			{
				echo "</a>";
			}
			echo "<div class='messages_text'>" . formatText($row->text) . "</div>";
			$firstFile = true;
			foreach ($attachedFiles as $file)
			{
				if ($file)
				{
					$uploadedFileQuery->execute(array
					(
						":id" => $file
					));
					$uploadedFileRow = $uploadedFileQuery->fetch();
					if ($uploadedFileRow)
					{
						if ($firstFile)
						{
							$firstFile = false;
							echo "
								<div class='messages_attachments'>
									<b>Anh&auml;nge:</b>
									<ul>
							";
						}
						echo "<li><a href='/uploads/" . $file . "/" . $uploadedFileRow->name . "'>" . escapeText($uploadedFileRow->title) . "</a></li>";
					}
				}
			}
			if (!$firstFile)
			{
				echo "
						</ul>
					</div>
				";
			}
			echo "</div>";
			
			$messageCount++;
			
			if ($messageCount == 1 and $id == -1)
			{
				break;
			}
		}
		
		if (!$messageCount)
		{
			if ($expired)
			{
				echo "<div class='error'>Die G&uuml;ltigkeit der Nachricht ist abgelaufen!</div>";
			}
			else
			{
				echo "<div class='error'>Keine Nachricht gefunden!</div>";
			}
			return false;
		}
		
		return true;
	}
}
?>