<h1>Interner Bereich</h1>

<h2>Letzte Nachricht</h2>
<?php
$messageManager = new MessageManager;
$messageManager->processEdit();
$messageManager->showMessage(-1);
$messageManager->addEditOptions();
?>