<?php
if (Constants::$getPageTitle)
{
	$title = "Links";
	return;
}

$links = array();
$query = Constants::$pdo->query("SELECT * FROM `links` ORDER BY `letter` ASC, `title` ASC");
while ($row = $query->fetch())
{
	$links[$row->letter][$row->url] = $row->title;
}

echo "<ul>";
foreach ($links as $letter => $urls)
{
	echo "<li>";
	echo "<b>" . strtoupper($letter) . "</b>";
	echo "<ul>";
	foreach ($urls as $url => $title)
	{
		echo "<li><a href=\"/links/" . $url . "\" target=\"_blank\">" . $title . "</a></li>";
	}
	echo "</ul>";
	echo "</li>";
}
echo "</ul>";
?>