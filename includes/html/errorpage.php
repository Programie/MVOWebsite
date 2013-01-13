<?php
if (Constants::$getPageTitle)
{
	$title =  "Nicht gefunden";
	return;
}
?>
<h1>Nicht gefunden</h1>
<p>Die gew&uuml;nschte Seite <b><?php echo implode(Constants::$pagePath, "/");?></b> wurde nicht gefunden!</p>