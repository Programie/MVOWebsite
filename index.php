<?php
define("ROOT_PATH", __DIR__);

require_once ROOT_PATH . "/includes/config.inc.php";
require_once ROOT_PATH . "/includes/Constants.class.php";
require_once ROOT_PATH . "/includes/database.php";
require_once ROOT_PATH . "/includes/functions.php";
require_once ROOT_PATH . "/includes/PageManager.class.php";
require_once ROOT_PATH . "/includes/Dates.class.php";
require_once ROOT_PATH . "/includes/Pictures.class.php";
require_once ROOT_PATH . "/includes/MenuBuilder.class.php";
require_once ROOT_PATH . "/includes/AccountManager.class.php";

session_start();

Constants::$pagePath = explode("/", $_GET["path"]);

foreach (Constants::$pagePath as $index => $page)
{
	if (!$page or $page[0] == ".")
	{
		unset(Constants::$pagePath[$index]);
	}
}

Constants::$pageManager = new PageManager(json_decode(file_get_contents(ROOT_PATH . "/includes/pages.json")));
Constants::$accountManager = new AccountManager();

$fullPageTitle = array("Musikverein Reichental");

if (empty(Constants::$pagePath))
{
	Constants::$pagePath = array("home");
}

require_once ROOT_PATH . "/includes/nonhtml.php";

$pageData = Constants::$pageManager->getPageData(Constants::$pagePath);
foreach ($pageData as $data)
{
	if (!$data->hasPermission)
	{
		break;
	}
	$fullPageTitle[] = $data->title;
}

require_once "includes/html/main.php";
?>