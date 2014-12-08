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
		<title><?php echo implode(PAGE_TITLE_SEPARATOR, $fullPageTitle); ?></title>
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
		<link rel="stylesheet" type="text/css" href="/files/style.css?md5=<?php echo md5($md5); ?>"/>
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
		<script type="text/javascript" src="/files/script.js?md5=<?php echo md5($md5); ?>"></script>
		<link rel="icon" href="/files/images/favicon.ico"/>
	</head>

	<body style="background-image: url(<?php echo $backgroundFile; ?>);">
		<div id="unsupported_browser_warning">
			<p><b>Warnung: Ihr Browser ist nicht auf dem aktuellsten Stand!</b></p>

			<p>Der von Ihnen verwendete Internetbrowser ist veraltet bzw. unterst&uuml;tzt nicht alle auf dieser Seite verwendeten Eigenschaften.</p>

			<p><a href="/update_browser" target="_blank">Klicken Sie hier um weitere Informationen zu erhalten.</a></p>
		</div>

		<div id="container">
			<div id="header">
				<div id="header_logo"></div>
				<div id="header_image"></div>
				<?php require_once "menu.php"; ?>
			</div>

			<div id="body">
				<div id="bodycontent_div1">
					<div id="bodycontent_div2">
						<?php Constants::$pageManager->includePage(0); ?>
					</div>
				</div>
			</div>

			<div id="footer">
				<div id="footercontent">
					<p>&copy; <?php echo date("Y"); ?> Musikverein "Orgelfels" Reichental e.V.</p>

					<p id="footerlinks">
						<a href="/home">Home</a>
						<a href="/imprint">Impressum</a>
					</p>
				</div>
			</div>
		</div>

		<div id="overlay_container">
			<div id="backtotop" title="Nach oben"></div>
			<div id="print" title="Drucken"></div>
		</div>
	</body>
</html>