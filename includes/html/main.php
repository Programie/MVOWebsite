<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
		<title><?php echo implode(PAGE_TITLE_SEPARATOR, $fullPageTitle);?></title>
		<link rel="stylesheet" type="text/css" href="/files/style.css"/>
		<script type="text/javascript" src="/files/scripts/jquery.js"></script>
		<script type="text/javascript" src="/files/scripts/jquery-ui.js"></script>
		<script type="text/javascript" src="/files/scripts/photobox.js"></script>
		<script type="text/javascript" src="/files/scripts/slimbox.js"></script>
		<script type="text/javascript" src="/files/scripts/tablesorter.js"></script>
		<script type="text/javascript" src="/files/scripts/general.js"></script>
	</head>
	<body>
		<div id="backgroundwrapper">
			<div id="background">
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
				?>
				<img id="backgroundimage" src="/files/backgrounds/<?php echo $backgroundType;?>/<?php echo $backgroundFile;?>" alt="Hintergrund"/>
				<div id="backgroundoverlay"></div>
			</div>
		</div>
		<div id="container">
			<?php require_once "header.php";?>
			<?php require_once "body.php";?>
			<?php require_once "footer.php";?>
		</div>
		<div id="backtotop">Nach oben</div>
	</body>
</html>