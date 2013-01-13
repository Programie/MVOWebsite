<?php
$menu = json_decode(file_get_contents(ROOT_PATH . "/includes/menu.json"));
new MenuBuilder($menu);
?>