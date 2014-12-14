<?php
if (Constants::$pagePath[2])
{
	echo "<h1>Nachricht</h1>";

	if (isset($_GET["sendinfo"]))
	{
		echo "<div class='alert-success'>Die Nachricht wurde erfolgreich gesendet.</div>";
	}
}
else
{
	echo "<h1>Alle Nachrichten</h1>";
}

$messageManager = new MessageManager;
$messageManager->processEdit();
$messageManager->showMessage(Constants::$pagePath[2]);
$messageManager->addEditOptions();