<!DOCTYPE html>
<?php
$backgroundType = getCurrentSeason();
$backgroundPath = ROOT_PATH . "/files/backgrounds/" . $backgroundType;
$backgroundFile = $_SESSION["backgroundFile"];
if (!$backgroundFile or !file_exists($backgroundPath . "/" . $backgroundFile))
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
	$_SESSION["backgroundFile"] = $backgroundFile;
}
$backgroundFile = "/files/backgrounds/" . $backgroundType . "/" . $backgroundFile;
?>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
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
		$jsList = array("errorreport", "jquery", "jquery-ui", "metadata", "colorbox", "photobox", "tablesorter", "tablesorter-widgets", "general");
		foreach ($jsList as $file)
		{
			$file = "/files/scripts/" . $file . ".js";
			echo "<script type='text/javascript' src='" . $file . "?md5=" . md5_file(ROOT_PATH . $file) . "'></script>";
		}
		?>
	</head>
	<body style="background-image: url(<?php echo $backgroundFile;?>);">
		<div id="container">
			<?php require_once "header.php";?>
			<?php require_once "body.php";?>
			<?php require_once "footer.php";?>
		</div>
		<div id="backtotop">Nach oben</div>
	</body>
</html>