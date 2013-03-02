<?php
class AccountManager
{
	private $loginFailed;
	private $permissions;
	private $userId;
	private $username;
	
	public function __construct()
	{
		if (isset($_POST["username"]) and isset($_POST["password"]))
		{
			$this->login($_POST["username"], $_POST["password"]);
		}
		else
		{
			if ($_SESSION["userId"])
			{
				$this->loginWithUserId($_SESSION["userId"]);
			}
		}
	}
	
	public function changePassword($newPassword, $oldPassword = null)
	{
		if ($oldPassword)
		{
			$query = Constants::$pdo->prepare("SELECT `password` FROM `users` WHERE `id` = :id");
			$query->execute(array
			(
				":id" => $this->userId
			));
			if (!$query->rowCount())
			{
				return false;
			}
			$row = $query->fetch();
			if ($row->password != $this->encrypt($this->userId, $oldPassword))
			{
				return false;
			}
		}
		
		$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password, `resetPasswordDate` = NULL WHERE `id` = :id");
		$query->execute(array
		(
			":id" => $this->userId,
			":password" => $this->encrypt($this->userId, $newPassword)
		));
		
		return true;
	}
	
	private function encrypt($userId, $password)
	{
		return hash("sha512", $userId . "_" . $password);
	}
	
	public function getPermissions()
	{
		$this->permissions = array();
		
		$query = Constants::$pdo->prepare("SELECT `permission` FROM `permissions` WHERE `userId` = :userId");
		$query->execute(array
		(
			":userId" => $this->userId
		));
		while ($row = $query->fetch())
		{
			$this->permissions[$row->permission] = true;
		}
		
		return $this->permissions;
	}
	
	public function getUserId()
	{
		return $this->userId;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	public function hasLoginFailed()
	{
		return $this->loginFailed;
	}
	
	public function hasPermission($permission)
	{
		// Check if a permission is required
		if (!$permission)
		{
			return true;
		}
		
		// Check if the user is logged in and has a permissions array
		if (!$this->userId or !is_array($this->permissions))
		{
			return false;
		}
		
		// Check if the user has all permissions (*)
		if ($this->permissions["*"])
		{
			return true;
		}
		
		if (!is_array($permission))
		{
			$permission = array($permission);
		}
		
		$permissionArray = $permission;
		foreach ($permissionArray as $permission)
		{
			// Check if only a login without any permissions is required
			if (!$permission or $permission == "1")
			{
				return true;
			}
			
			// Check if the required permission node is the same as one of the user's permission nodes or a (sub-)child of it
			// Example:
			// Requested: 'a.b.c'
			// User has permission 'a.b' -> 'c' is a child node of it -> Has permission
			// User has permission 'a.b.c' -> Exact match -> Has permission
			// User has permission 'a.b.c.d' -> a.b.c is a parent node -> Has no permission
			$permissionParts = explode(".", $permission);
			foreach ($permissionParts as $index => $part)
			{
				if ($this->permissions[implode(".", array_slice($permissionParts, 0, $index + 1))])
				{
					return true;
				}
			}
			
			// Check if the required permission node is the same as one of the user's permission nodes or a parent of it
			// Example:
			// Requested: 'a.b.c'
			// User has permission 'a.b' -> 'c' is a child node of it -> Has no permission
			// User has permission 'a.b.c' -> Exact match -> Has permission
			// User has permission 'a.b.c.d' -> 'a.b.c' is part of the path -> Has permission
			foreach ($this->permissions as $permissionString => $dummy)
			{
				$permissionParts = explode(".", $permissionString);
				foreach ($permissionParts as $index => $permissionString)
				{
					if ($permission == implode(".", array_slice($permissionParts, 0, $index +1)))
					{
						return true;
					}
				}
			}
		}
		
		// Permission node not found
		return false;
	}
	
	public function hasPermissionInArray($permissionArray, $prefix = "")
	{
		if ($perfix and $this->hasPermission($prefix))
		{
			return true;
		}
		
		foreach ($permissionArray as $permission)
		{
			if (!$permission)
			{
				continue;
			}
			
			if ($this->hasPermission(($prefix ? ($prefix . ".") : "") . $permission))
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function login($username, $password)
	{
		$this->loginFailed = false;
		$this->logout();
		$query = Constants::$pdo->prepare("SELECT `id`, `username`, `password` FROM `users` WHERE `username` = :username");
		$query->execute(array
		(
			":username" => $username
		));
		if (!$query->rowCount())
		{
			$this->loginFailed = true;
			return false;
		}
		$row = $query->fetch();
		if ($row->password != $this->encrypt($row->id, $password))
		{
			$this->loginFailed = true;
			return false;
		}
		$this->userId = $row->id;
		$this->username = $row->username;
		$_SESSION["userId"] = $row->id;
		$this->getPermissions();
		return true;
	}
	
	public function loginWithUserId($userId)
	{
		$this->logout();
		$query = Constants::$pdo->prepare("SELECT `username` FROM `users` WHERE `id` = :id");
		$query->execute(array
		(
			":id" => $userId
		));
		if (!$query->rowCount())
		{
			return false;
		}
		$row = $query->fetch();
		$this->userId = $userId;
		$this->username = $row->username;
		$_SESSION["userId"] = $userId;
		$this->getPermissions();
		return true;
	}
	
	public function logout()
	{
		$this->userId = null;
		$this->username = null;
		unset($_SESSION["userId"]);
		$this->permissions = array();
	}
}