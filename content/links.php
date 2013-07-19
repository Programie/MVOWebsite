<h1>Links</h1>

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
				<tr>
					<td><a href='/links/" . $row->id . "' target='_blank'>" . $row->title . "</a></td>
					<td>" . $row->town . "</td>
				</tr>
			";
	}
	?>
	</tbody>
</table>