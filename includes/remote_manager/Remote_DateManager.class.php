<?php
class Remote_DateManager
{
	public function deleteDate($params)
	{
		$query = Constants::$pdo->prepare("DELETE FROM `dates` WHERE `id` = :id");
		$query->execute(array
		(
			":id" => $params->id
		));
		if ($query->rowCount())
		{
			return "ok";
		}
	}
	
	public function getData()
	{
		$dates = array();
		
		$query = Constants::$pdo->query("
			SELECT
			`dates`.*,
			`locations`.`name` AS `location`
			FROM `dates`
			LEFT JOIN `locations` ON `locations`.`id` = `dates`.`locationId`
		");
		while ($row = $query->fetch())
		{
			$row->showInAttendanceList = (bool) $row->showInAttendanceList;
			$row->bold = (bool) $row->bold;
			unset($row->locationId);
			$dates[] = $row;
		}
		
		$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups` ORDER BY `title` ASC");
		$groups = $query->fetchAll();
		
		$query = Constants::$pdo->query("SELECT `name` FROM `locations` ORDER BY `name` ASC");
		$locations = $query->fetchAll();
		
		return array
		(
			"dates" => $dates,
			"groups" => $groups,
			"locations" => $locations
		);
	}
	
	public function setDate($params)
	{
		$locationId = 0;
		if ($params->location)
		{
			$query = Constants::$pdo->prepare("SELECT `id` FROM `locations` WHERE `name` = :name");
			$query->execute(array
			(
				":name" => $params->location
			));
			$row = $query->fetch();
			$locationId = $row->id;
			if (!$locationId)
			{
				$query = Constants::$pdo->prepare("INSERT INTO `locations` (`name`) VALUES(:name)");
				$query->execute(array
				(
					":name" => $params->location
				));
				$locationId = Constants::$pdo->lastInsertId();
			}
		}
		$queryParameters = array
		(
			":startDate" => $params->startDate,
			":endDate" => $params->endDate,
			":groups" => $params->groups,
			":title" => $params->title,
			":description" => $params->description,
			":locationId" => $locationId,
			":showInAttendanceList" => $params->showInAttendanceList,
			":bold" => $params->bold
		);
		if ($params->id)
		{
			$query = Constants::$pdo->prepare("
				UPDATE `dates` SET
				`startDate` = :startDate,
				`endDate` = :endDate,
				`groups` = :groups,
				`title` = :title,
				`description` = :description,
				`locationId` = :locationId,
				`showInAttendanceList` = :showInAttendanceList,
				`bold` = :bold
				WHERE `id` = :id
			");
			$queryParameters[":id"] = $params->id;
			$query->execute($queryParameters);
			if ($query->rowCount())
			{
				return "ok";
			}
		}
		else
		{
			$query = Constants::$pdo->prepare("
				INSERT INTO `dates`
				(`startDate`, `endDate`, `groups`, `title`, `description`, `locationId`, `showInAttendanceList`, `bold`)
				VALUES(:startDate, :endDate, :groups, :title, :description, :locationId, :showInAttendanceList, :bold)
			");
			$query->execute($queryParameters);
			if (Constants::$pdo->lastInsertId())
			{
				return "ok";
			}
		}
	}
}
?>