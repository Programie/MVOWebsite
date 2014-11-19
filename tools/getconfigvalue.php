<?php
require_once __DIR__ . "/../includes/config.inc.php";

if (!@$argv[1])
{
	die("Usage: " . $argv[0] . " <config name>");
	exit;
}

echo constant($argv[1]);
