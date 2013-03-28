<?php
class MessageManager
{
	public function showMessage($id)
	{
		$uploadedFileQuery = Constants::$pdo->prepare("SELECT `name`, `title` FROM `uploads` WHERE `id` = :id");
		$userGroupsQuery = Constants::$pdo->prepare("SELECT `title` FROM `usergroups` WHERE `name` = :name");
		
		$commonSql = "SELECT `messages`.`id`, `messages`.`date`, `messages`.`targetGroups`, `messages`.`text`, `messages`.`attachedFiles`, `users`.`id` AS `userId`, `users`.`firstName`, `users`.`lastName`, `users`.`email` FROM `messages` LEFT JOIN `users` ON `users`.`id` = `messages`.`userId`";
		if ($id == null or $id == -1)
		{
			$query = Constants::$pdo->query($commonSql . " ORDER BY `messages`.`id` DESC");
		}
		else
		{
			$query = Constants::$pdo->prepare($commonSql . " WHERE `messages`.`id` = :id");
			$query->execute(array
			(
				":id" => $id
			));
		}
		
		$messageCount = 0;
		
		while ($row = $query->fetch())
		{
			$targetGroups = explode(",", $row->targetGroups);
			$attachedFiles = explode(",", $row->attachedFiles);
			
			if (!Constants::$accountManager->hasPermissionInArray($targetGroups, "messages.view"))
			{
				continue;
			}
			
			$targets = array();
			foreach ($targetGroups as $groupName)
			{
				$userGroupsQuery->execute(array
				(
					":name" => $groupName
				));
				$userGroupsRow = $userGroupsQuery->fetch();
				$targets[] = $userGroupsRow->title ? $userGroupsRow->title : $groupName;
			}
			
			echo "<div class='messages_container'>";
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
						<div class='messages_header_sender'><b>Erstellt von:</b> " . $row->firstName . " " . $row->lastName . " [" . $row->email . "]</div>
						<div class='messages_header_date'><b>Zeit:</b> " . date("d.m.Y H:i:s", strtotime($row->date)) . "</div>
						<div class='messages_header_target'><b>Gesendet an:</b> " . implode(", ", $targets) . "</div>
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
						echo "<li><a href='/uploads/" . $file . "/" . $uploadedFileRow->name . "'>" . $uploadedFileRow->title . "</a></li>";
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
			echo "<div class='error'>Keine Nachricht gefunden!</div>";
			return false;
		}
		
		return true;
	}
}
?>