<?php
if (Constants::$pagePath[1] == "agreement")
{
	$file = ROOT_PATH . "/files/forms/Ausbildungsvereinbarung.pdf";
	if (file_exists($file))
	{
		header("Content-Type: application/pdf");
		readfile($file);
		exit;
	}
}