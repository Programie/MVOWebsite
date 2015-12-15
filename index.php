<?php
/**
 * The absolute path on the filesystem to the root of the website
 */
define("ROOT_PATH", __DIR__);
/**
 * The absolute path on the filesystem to the upload directory
 */
define("UPLOAD_PATH", ROOT_PATH . "/uploads");
/**
 * The base URL of the website (http|https + :// + hostname)
 */
define("BASE_URL", (@$_SERVER["HTTPS"] ? "https" : "http") . "://" . $_SERVER["SERVER_NAME"]);
/**
 * The maximum upload size in bytes
 */
define("MAX_UPLOAD_SIZE", min(intval(ini_get("upload_max_filesize")), intval(ini_get("post_max_size")), intval(ini_get("memory_limit"))));

try
{
	require_once ROOT_PATH . "/includes/config.inc.php";
	require_once ROOT_PATH . "/vendor/autoload.php";
	require_once ROOT_PATH . "/includes/Constants.class.php";
	require_once ROOT_PATH . "/includes/database.php";
	require_once ROOT_PATH . "/includes/functions.php";
	require_once ROOT_PATH . "/includes/PageManager.class.php";
	require_once ROOT_PATH . "/includes/Mail.class.php";
	require_once ROOT_PATH . "/includes/Dates.class.php";
	require_once ROOT_PATH . "/includes/Pictures.class.php";
	require_once ROOT_PATH . "/includes/AccountManager.class.php";
	require_once ROOT_PATH . "/includes/MessageManager.class.php";
	require_once ROOT_PATH . "/includes/NoteDirectory.class.php";
	require_once ROOT_PATH . "/includes/DBSessionHandler.class.php";

	// 10% (1/10) session garbage collection probability
	ini_set("session.gc_probability", 1);
	ini_set("session.gc_divisor", 10);

	$dbSessionHandler = new DBSessionHandler(Constants::$pdo);

	session_set_save_handler($dbSessionHandler, true);
	session_start();

	Constants::$pagePath = array();

	$pagePath = explode("/", $_GET["path"]);
	foreach ($pagePath as $page)
	{
		if ($page != "" and $page[0] != ".")
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

	$preHtmlFile = ROOT_PATH . "/includes/prehtml/" . Constants::$pagePath[0] . ".php";
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

	// Visit counter
	if (!preg_match("/bot|spider|crawler|curl|^$/i", $_SERVER["HTTP_USER_AGENT"]))
	{
		$query = Constants::$pdo->prepare("SELECT `id`, `userId` FROM `visits` WHERE `date` = CURDATE() AND `ip` = :ip");
		$query->execute(array(":ip" => $_SERVER["REMOTE_ADDR"]));
		if ($query->rowCount())
		{
			$row = $query->fetch();
			if (!$row->userId)
			{
				$row->userId = Constants::$accountManager->getUserId();
			}
			$query = Constants::$pdo->prepare("UPDATE `visits` SET `lastVisitDate` = NOW(), `lastVisitPath` = :path, `userId` = :userId WHERE `id` = :id");
			$query->execute(array(":path" => implode("/", Constants::$pagePath), ":userId" => $row->userId, ":id" => $row->id));
		}
		else
		{
			$query = Constants::$pdo->prepare("INSERT INTO `visits` (`ip`, `date`, `firstVisitDate`, `firstVisitPath`, `lastVisitDate`, `lastVisitPath`, `userId`) VALUES(:ip, CURDATE(), NOW(), :path, NOW(), :path, :userId)");
			$query->execute(array(":ip" => $_SERVER["REMOTE_ADDR"], ":path" => implode("/", Constants::$pagePath), ":userId" => Constants::$accountManager->getUserId()));
		}
	}

	ob_start(function ()
	{
		chdir(dirname($_SERVER["SCRIPT_FILENAME"])); // ob_start changes the working directory
	});

	require_once "includes/html/main.php";

	$content = ob_get_flush();

	if (defined("ADD_HTTP_HEADER"))
	{
		header(ADD_HTTP_HEADER);
	}

	echo $content;
}
catch (Exception $exception)
{
	ob_end_clean();
	readfile(ROOT_PATH . "/error500.html");
	error_log($exception);
}
?>