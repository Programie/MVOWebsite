<?php
$query = Constants::$pdo->prepare("SELECT `name`, `title` FROM `uploads` WHERE `id` = :id AND `name` = :name");
$query->execute(array(":id" => Constants::$pagePath[1], ":name" => Constants::$pagePath[2]));
if ($query->rowCount())
{
	$row = $query->fetch();
	$filename = UPLOAD_PATH . "/" . $row->name;
	if (file_exists($filename))
	{
		$file = fopen($filename, "r");
		{
			header("Content-Description: Datei herunterladen");
			header("Content-Type: application/octet-stream");
			header("Content-Disposition: attachment; filename=\"" . $row->title . "\"");
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