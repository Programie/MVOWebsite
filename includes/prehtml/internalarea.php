<?php
if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
{
	header("Location: " . BASE_URL . "/internalarea");
	exit;
}
if (Constants::$pagePath[1])
{
	switch (Constants::$pagePath[1])
	{
		case "logout":
			Constants::$accountManager->logout();
			header("Location: " . BASE_URL . "/internalarea");
			exit;
	}
}
else
{
	if (Constants::$accountManager->getUserId())
	{
		Constants::$pagePath[1] = "home";
	}
	else
	{
		Constants::$pagePath[1] = "login";
	}
}
?>