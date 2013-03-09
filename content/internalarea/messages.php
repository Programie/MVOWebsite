<?php
if (Constants::$pagePath[2])
{
	echo "<h1>Nachricht</h1>";
}
else
{
	echo "<h1>Alle Nachrichten</h1>";
}
$messageManager = new MessageManager;
$messageManager->showMessage(Constants::$pagePath[2]);
?>