<?php
define("ROOT_PATH", __DIR__);

require_once ROOT_PATH . "/includes/config.inc.php";
require_once ROOT_PATH . "/includes/database.php";
require_once ROOT_PATH . "/includes/Constants.class.php";
require_once ROOT_PATH . "/includes/functions.php";
require_once ROOT_PATH . "/includes/MenuBuilder.class.php";

session_start();

Constants::$pagePath = explode("/", $_GET["path"]);

foreach (Constants::$pagePath as $index => $page)
{
	if (!$page or $page[0] == ".")
	{
		unset(Constants::$pagePath[$index]);
	}
}

Constants::$getPageTitle = true;
$fullPageTitle = array("Musikverein Reichental");

if (empty(Constants::$pagePath))
{
	Constants::$pagePath[] = "home";
}

foreach (Constants::$pagePath as $index => $page)
{
	$title = "";
	$file = getValidContentFile(array_slice(Constants::$pagePath, 0, $index + 1), true);
	if (!$file)
	{
		break;
	}
	include $file;
	if ($title)
	{
		$fullPageTitle[] = $title;
	}
}

Constants::$getPageTitle = false;

require_once "includes/html/main.php";
?>