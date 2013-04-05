<?php
class Dates
{
	public static function getDates($year = null, $groups = null)
	{
		$query = Constants::$pdo->prepare("SELECT `dates`.`id`, `startDate`, `endDate`, `groups`, `showInAttendanceList`, `bold`, `title`, `description`, `locations`.`latitude` AS `locationLatitude`, `locations`.`longitude` AS `locationLongitude`, `locations`.`name` AS `locationName` FROM `dates` LEFT JOIN `locations` ON `locations`.`id` = `dates`.`locationId` WHERE (:year IS NULL OR YEAR(`startDate`) = :year OR YEAR(`endDate`) = :year) ORDER BY `startDate` ASC");
		$query->execute(array
		(
			":year" => $year
		));
		
		if (!$query->rowCount())
		{
			return null;
		}
		
		$dates = array();
		
		$nextEventFound = false;
		
		while ($row = $query->fetch())
		{
			$row->groups = explode(",", $row->groups);
			
			if ($groups and $groups[0])
			{
				$showGroup = false;
				if (in_array("public", $groups) and !$row->groups[0])
				{
					$showGroup = true;
				}
				foreach ($groups as $group)
				{
					foreach ($row->groups as $groupName)
					{
						if ($groupName == $group)
						{
							$showGroup = true;
							break;
						}
					}
				}
				if (!$showGroup)
				{
					continue;
				}
			}
			
			if (!Constants::$accountManager->hasPermissionInArray($row->groups, "dates"))
			{
				continue;
			}
			
			$locationData = new StdClass;
			$locationData->latitude = $row->locationLatitude;
			$locationData->longitude = $row->locationLongitude;
			$locationData->name = $row->locationName;
			
			$row->location = $locationData;
			
			unset($row->locationLatitude);
			unset($row->locationLongitude);
			unset($row->locationName);
			
			$row->startDate = strtotime($row->startDate);
			$row->endDate = strtotime($row->endDate);
			
			if ($row->endDate > $row->startDate)
			{
				$row->oldEvent = $row->endDate < time();
			}
			else
			{
				$row->oldEvent = $row->startDate < time();
			}
			
			if (!$nextEventFound and ($row->startDate >= time() or $row->endDate >= time()))
			{
				$row->nextEvent = true;
				$nextEventFound = true;
			}
			
			$dates[] = $row;
		}
		
		return $dates;
	}
	
	public static function getYears()
	{
		$years = array();
		
		$query = Constants::$pdo->query("SELECT YEAR(`startDate`) AS `year` FROM `dates` GROUP BY `year`");
		while ($row = $query->fetch())
		{
			$years[$row->year] = true;
		}
		
		return $years;
	}
}
?>