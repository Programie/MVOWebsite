<?php
if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
{
	if ($_GET["jumpto"])
	{
		header("Location: " . BASE_URL . "/" . $_GET["jumpto"]);
	}
	else
	{
		header("Location: " . BASE_URL . "/internalarea");
	}
	exit;
}

$userData = Constants::$accountManager->getUserData();
if ($userData->id and $userData->forcePasswordChange)
{
	$validPages = array("confirmemail", "editprofile", "logout");
	if (!in_array(Constants::$pagePath[1], $validPages))
	{
		header("Location: " . BASE_URL . "/internalarea/editprofile#editprofile_changepassword");
		exit;
	}
}

if (Constants::$pagePath[1])
{
	$file = ROOT_PATH . "/includes/prehtml/internalarea/" . Constants::$pagePath[1] . ".php";
	if (file_exists($file))
	{
		require_once $file;
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