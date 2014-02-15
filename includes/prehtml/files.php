<?php
switch (Constants::$pagePath[1])
{
	case "style.css":
		header("Content-Type: text/css; charset=utf-8");
		header("Cache-Control: public, max-age=86400"); // 1 day
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT"); // 1 day
		header("Pragma: ");
		$path = ROOT_PATH . "/files/css";
		$dir = scandir($path);
		foreach ($dir as $file)
		{
			if ($file[0] != "." and is_file($path . "/" . $file))
			{
				readfile($path . "/" . $file);
				echo "\n";
			}
		}
		exit;
	case "script.js":
		header("Content-Type: text/javascript");
		header("Cache-Control: public, max-age=86400"); // 1 day
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT"); // 1 day
		header("Pragma: ");
		$javaScriptFiles = array("errorreport", "jquery", "jquery-ui", "globalize", "globalize-de", "datepicker-de", "metadata", "jcrop", "colorbox", "photobox", "tablesorter", "tablesorter-widgets", "noty", "noty-layout", "noty-theme", "timepicker", "timepicker-de", "jquery.form", "jquery.contextmenu", "jstree", "fullcalendar", "splitter", "google-analytics", "general");
		$path = ROOT_PATH . "/files/scripts";
		foreach ($javaScriptFiles as $file)
		{
			$file = $path . "/" . $file . ".js";
			if (is_file($file))
			{
				readfile($file);
				echo "\n";
			}
		}
		exit;
}
?>