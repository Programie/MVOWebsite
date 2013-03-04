<?php
class MessageManager
{
	public function showMessage($id)
	{
		$userGroupsQuery = Constants::$pdo->prepare("SELECT `title` FROM `usergroups` WHERE `name` = :name");
		
		$commonSql = "SELECT `messages`.`id`, `messages`.`date`, `messages`.`targetGroups`, `messages`.`text`, `users`.`firstName`, `users`.`lastName` FROM `messages` LEFT JOIN `users` ON `users`.`id` = `messages`.`userId`";
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
			$targetGroups = explode("\n", convertLinebreaks($row->targetGroups));
			
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
			
			echo "
				<div class='messages_container'>
					<a href='/internarea/messages/" . $row->id . "' class='messages_header' title='Klicken um nur diese Nachricht anzuzeigen'>
						<div class='messages_header'>
							<div class='messages_header_sender'><b>Erstellt von:</b> " . $row->firstName . " " . $row->lastName . "</div>
							<div class='messages_header_date'><b>Datum:</b> " . date("d.m.Y H:i:s", strtotime($row->date)) . "</div>
							<div class='messages_header_target'><b>Gesendet an:</b> " . implode(", ", $targets) . "</div>
						</div>
					</a>
					<div class='messages_text'>" . formatText($row->text) . "</div>
				</div>
			";
			
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