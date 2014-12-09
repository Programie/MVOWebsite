<?php
$mustache = new Mustache_Engine();

echo $mustache->render(file_get_contents(__DIR__ . "/groups.html"), json_decode(file_get_contents(__DIR__ . "/groups.json")));