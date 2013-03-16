<h1>Formulare</h1>

<?php
if (Constants::$pagePath[2])
{
	echo "<div class='error'>Die Datei <b>" . Constants::$pagePath[2] . "</b> wurde nicht gefunden oder du hast nicht die erforderlichen Rechte um diese Datei herunterzuladen!</div>";
}

$forms = array();

$query = Constants::$pdo->query("SELECT * FROM `forms`");
while ($row = $query->fetch())
{
	if (!Constants::$accountManager->hasPermission("forms." . $row->name))
	{
		continue;
	}
	
	$forms[] = $row;
}

if (empty($forms))
{
	echo "<div class='error'>Keine Formulare vorhanden!</div>";
}
else
{
	echo "
		<table class='table {sortlist: [[0,0]]}'>
			<thead>
				<tr>
					<th>Name</th>
					<th>Typ</th>
				</tr>
			</thead>
			<tbody>
	";
	foreach ($forms as $row)
	{
		$fileInfo = pathinfo($row->filename);
		echo "
			<tr>
				<td><a href='/internalarea/forms/" . $row->filename . "'>" . $row->title . "</a></td>
				<td>" . getFileType($fileInfo["extension"]) . "</td>
			</tr>
		";
	}
	echo "
			</tbody>
		</table>
	";
}
?>