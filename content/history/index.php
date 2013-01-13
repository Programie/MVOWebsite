<?php
if (Constants::$getPageTitle)
{
	$title =  "Chronik";
	return;
}
if (Constants::$pagePath[1])
{
	include getValidContentFile(Constants::$pagePath[0] . "/" . Constants::$pagePath[1], false);
	return;
}
?>
<h1>Chronik</h1>
<ul>
	<li><a href="/history/short">Kurze Chronik</a></li>
	<li><a href="/history/long">Ausf&uuml;hrliche Chronik</a></li>
</ul>