<?php
class MessageManager
{
	public function addEditOptions()
	{
		if (Constants::$accountManager->hasPermission("messages.delete"))
		{
			echo "
				<div id='message-hide'>
					<p>Soll die ausgew&auml;hlte Nachricht wirklich ausgeblendet werden?</p>
					
					<div id='messages-hide-info'></div>
					
					<p><b>Hinweis:</b> Die Nachricht kann nur &uuml;ber die Datenbank wiederhergestellt werden!</p>
					
					<form id='message-hide-form' method='post' onsubmit='return false'>
						<input type='hidden' id='message-hide-id' name='hideMessageId'/>
					</form>
				</div>
				
				<div id='message-edit-contextmenu'>
					<ul>
						<li id='message-edit-contextmenu-hide'><img src='/files/images/contextmenu/trash.png'/> Ausblenden</li>
					</ul>
				</div>
				
				<script type='text/javascript'>
					$('#message-hide').dialog(
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
								$('#message-hide-form')[0].submit();
							},
							'Abbrechen' : function()
							{
								$(this).dialog('close');
							}
						}
					});
					
					$('.message-container').contextMenu('message-edit-contextmenu',
					{
						bindings :
						{
							'message-edit-contextmenu-hide' : function(trigger)
							{
								$('#message-hide-info').html($(trigger).find('.message-header-container').html());
								$('#message-hide-id').val($(trigger).data('messageid'));
								$('#message-hide').dialog('open');
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
			if ($_POST["hideMessageId"])
			{
				$query = Constants::$pdo->prepare("UPDATE `messages` SET `enabled` = '0' WHERE `id` = :id");
				$query->execute(array
				(
					":id" => $_POST["hideMessageId"]
				));

				echo "<div class='alert-success'>Die Nachricht wurde erfolgreich ausgeblendet.</div>";
			}
		}
	}

	public function showMessage($id = null)
	{
		$messageTargetQuery = Constants::$pdo->prepare("
			SELECT `users`.`id`, `firstName`, `lastName`
			FROM `messagetargets`
			LEFT JOIN `users` ON `users`.`id` = `messagetargets`.`userId`
			WHERE `messageId` = :messageId
		");

		$attachmentsQuery = Constants::$pdo->prepare("
			SELECT `uploads`.`id`, `name`, `title`
			FROM `messagefiles`
			LEFT JOIN `uploads` ON `uploads`.`id` = `messagefiles`.`fileId`
			WHERE `messageId` = :messageId
		");

		$query = Constants::$pdo->prepare("
			SELECT
				`messages`.`id`,
				`messages`.`date`,
				`messages`.`text`,
				`users`.`id` AS `userId`,
				`users`.`firstName`,
				`users`.`lastName`,
				`users`.`email`
			FROM `messages`
			LEFT JOIN `users` ON `users`.`id` = `messages`.`userId`
			WHERE
				`messages`.`enabled` AND
				(:id IS NULL OR :id = -1 OR `messages`.`id` = :id)
			ORDER BY `messages`.`id` DESC
		");

		$query->execute(array
		(
			":id" => $id
		));

		$mustache = new Mustache_Engine;

		$found = false;

		while ($row = $query->fetch())
		{
			$messageTargetQuery->execute(array
			(
				":messageId" => $row->id
			));

			$allowed = false;

			// The user is the sending user -> Allow to see the message
			if ($row->userId == Constants::$accountManager->getUserId())
			{
				$allowed = true;
			}

			$recipients = array();

			while ($targetRow = $messageTargetQuery->fetch())
			{
				// The user is the receiving user -> Allow to see the message
				if ($targetRow->id == Constants::$accountManager->getUserId())
				{
					$allowed = true;
				}

				$recipients[] = $targetRow;
			}

			if (!$allowed)
			{
				continue;
			}

			usort($recipients, function($item1, $item2)
			{
				if ($item1->lastName > $item2->lastName)
				{
					return 1;
				}

				if ($item1->lastName < $item2->lastName)
				{
					return -1;
				}

				if ($item1->firstName > $item2->firstName)
				{
					return 1;
				}

				if ($item1->firstName < $item2->firstName)
				{
					return -1;
				}

				return 0;
			});

			foreach ($recipients as &$recipient)
			{
				$recipient = $recipient->firstName . " " . $recipient->lastName;
			}

			$hiddenRecipients = array_slice($recipients, 10);

			// TODO: Find a better way instead usage of implode()
			$row->recipients = array
			(
				"limited" => implode(", ", array_slice($recipients, 0, 10)) . (empty($hiddenRecipients) ? "" : ","),
				"hidden" => implode(", ", $hiddenRecipients),
				"hiddenCount" => count($hiddenRecipients)
			);

			$attachmentsQuery->execute(array
			(
				":messageId" => $row->id
			));

			$row->attachments = $attachmentsQuery->fetchAll();

			if (file_exists(ROOT_PATH . "/files/profilepictures/" . $row->userId . ".jpg"))
			{
				$row->avatarUrl = "/getprofilepicture/" . $row->userId . "/" . md5_file(ROOT_PATH . "/files/profilepictures/" . $row->userId . ".jpg");
			}
			else
			{
				$row->avatarUrl = "/getprofilepicture/default/" . md5_file(ROOT_PATH . "/files/profilepictures/default.jpg");
			}

			$row->text = formatText($row->text);

			echo $mustache->render(file_get_contents(__DIR__ . "/templates/message.html"), $row);

			$found = true;

			// -1 means show only the last message
			if ($id == -1)
			{
				break;
			}
		}

		if (!$found)
		{
			echo "<div class='alert-error'>Keine Nachrichten verf&uuml;gbar!</div>";
		}
	}
}