<h1>Links</h1>

<?php
if (Constants::$pagePath[1])
{
	echo "<p class='alert-error'>Der angeforderte Link wurde nicht gefunden!</p>";

	setAdditionalHeader("HTTP/1.1 404 Not Found");
}
?>

<table id="links_table" class="table tablesorter {sortlist: [[1,0],[0,0]]}">
	<thead>
		<tr>
			<th>Name</th>
			<th>Ort</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$query = Constants::$pdo->query("SELECT * FROM `links`");
		while ($row = $query->fetch())
		{
			echo "
					<tr class='odd-even'>
						<td><a href='/links/" . $row->id . "' target='_blank'>" . $row->title . "</a></td>
						<td>" . $row->town . "</td>
					</tr>
				";
		}
		?>
	</tbody>
</table>