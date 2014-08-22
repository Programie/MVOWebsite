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
		elseif (isset($_SERVER["PHP_AUTH_USER"]) and isset($_SERVER["PHP_AUTH_PW"]))
		{
			$this->login($_SERVER["PHP_AUTH_USER"], $_SERVER["PHP_AUTH_PW"]);
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
			if (!$this->checkPassword($oldPassword))
			{
				return false;
			}
		}

		$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password, `resetPasswordDate` = NULL, `forcePasswordChange` = '0' WHERE `id` = :id");
		$query->execute(array(":id" => $this->userId, ":password" => $this->encrypt($this->userId, $newPassword)));

		$userData = $this->getUserData();

		$mail = new Mail("Passwort geändert");
		$mail->setTemplate("password-changed");
		$mail->addReplacement("FIRSTNAME", $userData->firstName);
		$mail->setTo($userData->email);
		$mail->send();

		return true;
	}

	public function checkPassword($password, $userId = null)
	{
		if (!$userId)
		{
			$userId = $this->userId;
		}

		$query = Constants::$pdo->prepare("SELECT `password` FROM `users` WHERE `id` = :id");
		$query->execute(array(":id" => $userId));

		if (!$query->rowCount())
		{
			return false;
		}

		$row = $query->fetch();
		if (substr($row->password, 0, 4) == "md5:")
		{
			if (substr($row->password, 4) == md5($password))
			{
				// Update password to latest encryption method
				$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password WHERE `id` = :id");
				$query->execute(array(":id" => $userId, ":password" => $this->encrypt($userId, $password)));

				return true;
			}
		}
		else
		{
			if ($row->password == $this->encrypt($userId, $password))
			{
				return true;
			}
		}

		return false;
	}

	public function changeUsername($newUsername)
	{
		$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `username` = :username");
		$query->execute(array(":username" => $newUsername));

		if ($query->rowCount())
		{
			$row = $query->fetch();
			if ($row->id != $this->getUserId())
			{
				return false;
			}
		}

		$query = Constants::$pdo->prepare("UPDATE `users` SET `username` = :username WHERE `id` = :id");
		$query->execute(array(":username" => $newUsername, ":id" => $this->getUserId()));

		return true;
	}

	public function encrypt($userId, $password)
	{
		return hash("sha512", $userId . "_" . $password);
	}

	public function getCalendarToken($generateNew = false)
	{
		$query = Constants::$pdo->prepare("SELECT `calendarToken` FROM `users` WHERE `id` = :id");
		$query->execute(array(":id" => $this->userId));
		$row = $query->fetch();
		$token = $row->calendarToken;

		if (!$token or $generateNew)
		{
			$token = TokenManager::generateToken();
			$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `calendarToken` = :calendarToken AND `id` != :id");
			$query->execute(array(":calendarToken" => $token, ":id" => $this->userId));
			if ($query->rowCount())
			{
				return $this->getCalendarToken(true);
			}
			$query = Constants::$pdo->prepare("UPDATE `users` SET `calendarToken` = :calendarToken WHERE `id` = :id");
			$query->execute(array(":calendarToken" => $token, ":id" => $this->userId));
		}

		return $token;
	}

	public function getPermissions()
	{
		$this->permissions = array();

		$query = Constants::$pdo->prepare("SELECT `permission` FROM `permissions` WHERE `userId` = :userId");
		$query->execute(array(":userId" => $this->userId));
		while ($row = $query->fetch())
		{
			$this->permissions[$row->permission] = true;
		}

		return $this->permissions;
	}

	public function getUserData($userId = null)
	{
		if (!$userId)
		{
			$userId = $this->userId;
		}

		$query = Constants::$pdo->prepare("SELECT * FROM `users` WHERE `id` = :id");
		$query->execute(array(":id" => $userId));

		return $query->fetch();
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
		$permissionArray = $permission;

		// Check if a permission is required
		if (!$permissionArray)
		{
			return true;
		}

		// Make sure the variable is a array
		if (!is_array($permissionArray))
		{
			$permissionArray = array($permissionArray);
		}

		// Check if the user is logged in and has a permissions array
		if (!$this->userId or !is_array($this->permissions))
		{
			return false;
		}

		// Check if this permission has been explicit revoked
		foreach ($permissionArray as $permission)
		{
			if ($this->permissions["-" . $permission])
			{
				return false;
			}
		}

		// Check if the user has all permissions (*)
		if ($this->permissions["*"])
		{
			return true;
		}

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
					if ($permission == implode(".", array_slice($permissionParts, 0, $index + 1)))
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
		foreach ($permissionArray as $permission)
		{
			if (!$permission)
			{
				return true;
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
		$query = Constants::$pdo->prepare("SELECT `id`, `username`, `password` FROM `users` WHERE `enabled` AND `username` = :username");
		$query->execute(array(":username" => $username));
		if (!$query->rowCount())
		{
			$this->loginFailed = true;

			return false;
		}
		$row = $query->fetch();
		if (!$this->checkPassword($password, $row->id))
		{
			$this->loginFailed = true;

			return false;
		}
		$this->userId = $row->id;
		$this->username = $row->username;
		$this->postLogin();

		return true;
	}

	public function loginWithUserId($userId)
	{
		$this->logout();
		$query = Constants::$pdo->prepare("SELECT `username` FROM `users` WHERE `id` = :id AND `enabled`");
		$query->execute(array(":id" => $userId));
		if (!$query->rowCount())
		{
			return false;
		}
		$row = $query->fetch();
		$this->userId = $userId;
		$this->username = $row->username;
		$this->postLogin();

		return true;
	}

	public function logout()
	{
		$this->userId = null;
		$this->username = null;
		unset($_SESSION["userId"]);
		$this->permissions = array();
	}

	private function postLogin()
	{
		$_SESSION["userId"] = $this->userId;
		$this->getPermissions();

		$query = Constants::$pdo->prepare("UPDATE `users` SET `lastOnline` = NOW() WHERE `id` = :id");
		$query->execute(array(":id" => $this->userId));
	}
}