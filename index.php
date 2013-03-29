<?php
define("ROOT_PATH", __DIR__);
define("UPLOAD_PATH", ROOT_PATH . "/uploads");
define("BASE_URL", (@$_SERVER["HTTPS"] ? "https" : "http") . "://" . $_SERVER["SERVER_NAME"]);

require_once ROOT_PATH . "/includes/config.inc.php";
require_once ROOT_PATH . "/includes/Constants.class.php";
require_once ROOT_PATH . "/includes/database.php";
require_once ROOT_PATH . "/includes/functions.php";
require_once ROOT_PATH . "/includes/PageManager.class.php";
require_once ROOT_PATH . "/includes/Mail.class.php";
require_once ROOT_PATH . "/includes/Dates.class.php";
require_once ROOT_PATH . "/includes/Pictures.class.php";
require_once ROOT_PATH . "/includes/MenuBuilder.class.php";
require_once ROOT_PATH . "/includes/AccountManager.class.php";
require_once ROOT_PATH . "/includes/MessageManager.class.php";
require_once ROOT_PATH . "/includes/NoteDirectory.class.php";

session_start();

Constants::$pagePath = array();

$pagePath = explode("/", $_GET["path"]);
foreach ($pagePath as $page)
{
	if ($page and $page[0] != ".")
	{
		Constants::$pagePath[] = $page;
	}
}

Constants::$pageManager = new PageManager(json_decode(file_get_contents(ROOT_PATH . "/includes/pages.json")));
Constants::$accountManager = new AccountManager();

$fullPageTitle = array("Musikverein Reichental");

if (empty(Constants::$pagePath))
{
	Constants::$pagePath = array("home");
}

$preHtmlFile = ROOT_PATH . "/includes/prehtml/" . Constants::$pagePath[0]. ".php";
if (file_exists($preHtmlFile))
{
	require_once $preHtmlFile;
}

$pageData = Constants::$pageManager->getPageData(Constants::$pagePath);
foreach ($pageData as $data)
{
	if (!$data->hasPermission)
	{
		break;
	}
	$fullPageTitle[] = $data->title;
}

if (!empty($pageData))
{
	$redirect = $pageData[count($pageData) - 1]->redirect;
	if ($redirect)
	{
		if ($redirect[0] == "/")
		{
			$redirect = BASE_URL . $redirect;
		}
		header("Location: " . $redirect);
		exit;
	}
}

require_once "includes/html/main.php";
?>