<?php
class DBSessionHandler implements SessionHandlerInterface
{
	/**
	 * @var PDO Instance of PDO connected to the main database
	 */
	private $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function close()
	{
		return true;
	}

	public function destroy($sessionId)
	{
		$query = $this->pdo->prepare("
			DELETE FROM `sessions`
			WHERE `sessionId` = :sessionId
		");

		$query->execute(array
		(
			":sessionId" => $sessionId
		));

		return true;
	}

	public function gc($maxLifeTime)
	{
		$query = $this->pdo->prepare("
			DELETE FROM `sessions`
			WHERE TIMESTAMPDIFF(SECOND, `date`, NOW()) >= :maxLifeTime
		");

		$query->execute(array
		(
			":maxLifeTime" => $maxLifeTime
		));

		return true;
	}

	public function open($savePath, $name)
	{
		return true;
	}

	public function read($sessionId)
	{
		$query = $this->pdo->prepare("
			SELECT `data`
			FROM `sessions`
			WHERE `id` = :id
		");

		$query->execute(array
		(
			":id" => $sessionId
		));

		if (!$query->rowCount())
		{
			return "";
		}

		return $query->fetch()->data;
	}

	public function write($sessionId, $data)
	{
		$query = $this->pdo->prepare("
			REPLACE INTO `sessions`
			SET
				`id` = :id,
				`date` = NOW(),
				`data` = :data
		");

		$query->execute(array
		(
			":id" => $sessionId,
			":data" => $data
		));
	}
}