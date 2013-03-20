<?php
class Remote_UserManager
{
	public function getUsersList()
	{
		$data = array();
		
		$query = Constants::$pdo->query("SELECT * FROM `users`");
		while ($row = $query->fetch())
		{
			unset($row->password);
			$row->id = (int) $row->id;
			$data[] = $row;
		}
		
		return $data;
	}
	
	public function getUserPermissions($params)
	{
		$data = array();
		
		$query = Constants::$pdo->prepare("SELECT * FROM `permissions` WHERE `userId` = :userId");
		$query->execute(array
		(
			":userId" => $params->userId
		));
		while ($row = $query->fetch())
		{
			$data[] = $row;
		}
		
		return $data;
	}
	
	public function setPermissions($params)
	{
		$query = Constants::$pdo->query("TRUNCATE TABLE `permissions`");
		
		$query = Constants::$pdo->prepare("INSERT INTO `permissions` (`userId`, `permission`) VALUES(:userId, :permission)");
		foreach ($params->users as $user)
		{
			foreach ($user->permissions as $permission)
			{
				$query->execute(array
				(
					":userId" => $user->id,
					":permission" => $permission
				));
			}
		}
	}
}
?>