<?php
switch (Constants::$pagePath[1])
{
	case "style.css":
		header("Content-Type: text/css; charset=utf-8");
		header("Cache-Control: public, max-age=86400"); // 1 day
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT"); // 1 day
		header("Pragma: ");

		$path = ROOT_PATH . "/files/css";

		$files = array
		(
			"alerts",
			"colorbox",
			"dropzone",
			"elusive-webfont",
			"fullcalendar",
			"jcrop",
			"jquery-ui",
			"jstree",
			"photobox",
			"polaroids",
			"timepicker",
			"global",
			"main",
			"internal-area",
			"misc"
		);

		foreach ($files as $file)
		{
			$file = $path . "/" . $file . ".css";
			if (is_file($file))
			{
				readfile($file);
				echo "\n";
			}
		}
		exit;
	case "script.js":
		header("Content-Type: text/javascript");
		header("Cache-Control: public, max-age=86400"); // 1 day
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT"); // 1 day
		header("Pragma: ");

		$path = ROOT_PATH . "/files/scripts";

		$files = array
		(
			"errorreport",
			"jquery",
			"jquery-ui",
			"globalize",
			"globalize-de",
			"datepicker-de",
			"metadata",
			"jcrop",
			"colorbox",
			"photobox",
			"tablesorter",
			"tablesorter-widgets",
			"noty",
			"noty-layout",
			"noty-theme",
			"timepicker",
			"timepicker-de",
			"jquery.form",
			"jquery.contextmenu",
			"jstree",
			"fullcalendar",
			"dropzone",
			"google-analytics",
			"general"
		);

		foreach ($files as $file)
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