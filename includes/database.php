<?php
try
{
	Constants::$pdo = new PDO(MYSQL_DSN, MYSQL_USERNAME, MYSQL_PASSWORD);
	Constants::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	Constants::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
	Constants::$pdo->query("SET NAMES utf8");
}
catch (PDOException $exception)
{
	error_log($exception);

	if (ENVIRONMENT == "dev")
	{
		die("Database connection failed: " . $exception);
	}
	else
	{
		die("Database connection failed!");
	}
}
?>
