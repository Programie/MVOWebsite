<!DOCTYPE html>
<?php
$backgroundType = getCurrentSeason();
$backgroundPath = ROOT_PATH . "/files/backgrounds/" . $backgroundType;
$backgroundFile = $_SESSION["backgroundFile"];
if (!$backgroundFile or $_SESSION["backgroundType"] != $backgroundType or !file_exists($backgroundPath . "/" . $backgroundFile))
{
	$dir = scandir($backgroundPath);
	$files = array();
	foreach ($dir as $index => $file)
	{
		if ($file[0] == ".")
		{
			unset($dir[$index]);
		}
		else
		{
			$files[] = $file;
		}
	}
	$backgroundFile = $files[rand(0, count($dir) - 1)];
	$_SESSION["backgroundType"] = $backgroundType;
	$_SESSION["backgroundFile"] = $backgroundFile;
}
$backgroundFile = "/files/backgrounds/" . $backgroundType . "/" . $backgroundFile;
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo implode(PAGE_TITLE_SEPARATOR, $fullPageTitle);?></title>
		<?php
		$path = ROOT_PATH . "/files/css";
		$md5 = "";
		$dir = scandir($path);
		foreach ($dir as $file)
		{
			if ($file[0] != "." and is_file($path . "/" . $file))
			{
				$md5 .= md5_file($path . "/" . $file);
			}
		}
		?>
		<link rel="stylesheet" type="text/css" href="/files/style.css?md5=<?php echo md5($md5);?>"/>
		<?php
		$path = ROOT_PATH . "/files/scripts";
		$md5 = "";
		$dir = scandir($path);
		foreach ($dir as $file)
		{
			if ($file[0] != "." and is_file($path . "/" . $file))
			{
				$md5 .= md5_file($path . "/" . $file);
			}
		}
		?>
		<script type="text/javascript" src="/files/script.js?md5=<?php echo md5($md5);?>"></script>
		<link rel="icon" href="/files/images/favicon.ico"/>
	</head>
	<body style="background-image: url(<?php echo $backgroundFile;?>);">
		<div id="notification"></div>
		<div id="container">
			<?php require_once "header.php";?>
			<?php require_once "body.php";?>
			<?php require_once "footer.php";?>
		</div>
		<div id="overlay_container">
			<div id="backtotop" title="Nach oben"></div>
			<div id="print" title="Drucken"></div>
		</div>
	</body>
</html>