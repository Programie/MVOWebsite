<?php
if (Constants::$pagePath[1])
{
	Constants::$pageManager->includePage(1);
	return;
}
?>
<h1>Chronik</h1>
<ul>
	<li><a href="/history/short">Kurze Chronik</a></li>
	<li><a href="/history/long">Ausf&uuml;hrliche Chronik</a></li>
</ul>