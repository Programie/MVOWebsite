<?php
$file = ROOT_PATH . "/files/profilepictures/" . Constants::$pagePath[1] . ".jpg";
if (file_exists($file))
{
	if (md5_file($file) == Constants::$pagePath[2])
	{
		header("Content-Type: image/jpeg");
		header("Cache-Control: public, max-age=86400"); // 1 day
		header("Expires: " . gmdate("D, d M Y H:i:s", time() + 86400) . " GMT"); // 1 day
		header("Pragma: ");
		readfile($file);
	}
	else
	{
		header("HTTP/1.1 403 Forbidden");
	}
}
else
{
	header("HTTP/1.1 404 Not Found");
}
exit;
?>