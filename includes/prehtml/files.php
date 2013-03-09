<?php
switch (Constants::$pagePath[1])
{
	case "style.css":
		header("Content-Type: text/css; charset=utf-8");
		$path = ROOT_PATH . "/files/css";
		$dir = scandir($path);
		foreach ($dir as $file)
		{
			if ($file[0] != "." and is_file($path . "/" . $file))
			{
				readfile($path . "/" . $file);
				echo "\n\n";
			}
		}
		exit;
	case "script.js":
		header("Content-Type: text/javascript");
		echo "SCRIPT";
		exit;
}
?>