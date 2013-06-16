<?php
Constants::$accountManager->logout();
header("Location: " . BASE_URL . "/internalarea");
?>