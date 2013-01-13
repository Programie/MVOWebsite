<?php
if (Constants::$getPageTitle)
{
	$title = "Home";
	return;
}
$file = ROOT_PATH . "/files/custom_html/home.html";
if (file_exists($file))
{
	readfile($file);
}
?>
<div id="home_picture"></div>