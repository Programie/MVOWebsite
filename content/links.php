<?php
$links = array();
$query = Constants::$pdo->query("SELECT * FROM `links` ORDER BY `letter` ASC, `title` ASC");
while ($row = $query->fetch())
{
	$links[$row->letter][] = $row;
}

echo "<h1>Links</h1>";

echo "<ul>";
foreach ($links as $letter => $urls)
{
	echo "<li>";
	echo "<b>" . strtoupper($letter) . "</b>";
	echo "<ul>";
	foreach ($urls as $row)
	{
		echo "<li><a href='/links/" . $row->id . "' target='_blank'>" . $row->title . "</a></li>";
	}
	echo "</ul>";
	echo "</li>";
}
echo "</ul>";
?>