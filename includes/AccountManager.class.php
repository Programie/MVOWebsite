<?php
class AccountManager
{
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
	
	public function changePassword($oldPassword, $newPassword)
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
		$query = Constants::$pdo->prepare("UPDATE `users` SET `password` = :password WHERE `id` = :id");
		$query->execute(array
		(
			":id" => $this->userId,
			":password" => $this->encrypt($this->userId, $newPassword)
		));
		return true;
	}
	
	public function getUserId()
	{
		return $this->userId;
	}
	
	public function getUsername()
	{
		return $this->username;
	}
	
	private function encrypt($userId, $password)
	{
		return hash("sha512", $userId . "_" . $password);
	}
	
	public function login($username, $password)
	{
		$this->logout();
		$query = Constants::$pdo->prepare("SELECT `id`, `username`, `password` FROM `users` WHERE `username` = :username");
		$query->execute(array
		(
			":username" => $username
		));
		if (!$query->rowCount())
		{
			return false;
		}
		$row = $query->fetch();
		if ($row->password != $this->encrypt($row->id, $password))
		{
			return false;
		}
		$this->userId = $row->id;
		$this->username = $row->username;
		$_SESSION["userId"] = $row->id;
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
		$_SESSION["userId"] = $row->id;
		return true;
	}
	
	public function logout()
	{
		$this->userId = null;
		$this->username = null;
		unset($_SESSION["userId"]);
	}
}