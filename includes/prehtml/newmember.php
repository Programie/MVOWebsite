<?php
if (Constants::$pagePath[1] == "form")
{
	$filename = ROOT_PATH . "/files/forms/Beitrittserklaerung.pdf";
	if (file_exists($filename))
	{
		$file = fopen($filename, "r");
		{
			header("Content-Description: Formular herunterladen");
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"Beitrittserklaerung.pdf\"");
			header("Content-Length: " . filesize($filename));
			header("Content-Transfer-Encoding: chunked");
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Pragma: public");
			while ($chunk = fread($file, 4096))
			{
				echo $chunk;
			}
			fclose($file);
			exit;
		}
	}
}
?>