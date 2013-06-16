<?php
if (Constants::$pagePath[2])
{
	$query = Constants::$pdo->prepare("SELECT `name` FROM `forms` WHERE `filename` = :filename");
	$query->execute(array
	(
		":filename" => Constants::$pagePath[2]
	));
	$row = $query->fetch();
	if (Constants::$accountManager->hasPermission("forms." . $row->name))
	{
		$filename = ROOT_PATH . "/files/forms/" . Constants::$pagePath[2];
		if (file_exists($filename))
		{
			$file = fopen($filename, "r");
			{
				header("Content-Description: Formular herunterladen");
				header("Content-Type: application/octet-stream");
				header("Content-Disposition: attachment; filename=\"" . Constants::$pagePath[2] . "\"");
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
}
?>