<?php
class NoteDirectory
{
	public function __construct($columns, $titles, $showInGroups)
	{
		echo "
			<div class='info no-print'>Klicke auf einen Titel um weitere Details anzuzeigen.</div>
			<table id='notedirectory_table' class='table {sortlist: [[0,0]]}'>
		";
		$this->createHeader($columns);
		if ($showInGroups)
		{
			$this->list_categories($columns, $titles);
		}
		else
		{
			$this->list_normal($columns, $titles);
		}
		echo "</table>";
	}
	
	public function createHeader($columns)
	{
		echo "
			<thead>
				<tr>
		";
		foreach ($columns as $columnTitle)
		{
			echo "<th>" . escapeText($columnTitle) . "</th>";
		}
		echo "
				</tr>
			</thead>
		";
	}
	
	public function list_categories($columns, $titles)
	{
		$categories = array();
		foreach ($titles as $row)
		{
			$categories[$row->category][] = $row;
		}
		
		foreach ($categories as $category => $titles)
		{
			if (!$category)
			{
				$category = "Ohne Kategorie";
			}
			echo "
				<tbody class='tablesorter-infoOnly'>
					<tr>
						<th colspan='" . count($columns) . "'>" . escapeText($category) . "</th>
					</tr>
				</tbody>
			";
			$this->list_normal($columns, $titles);
		}
	}
	
	public function list_normal($columns, $titles)
	{
		echo "<tbody>";
		foreach ($titles as $index => $row)
		{
			echo "<tr class='pointer' onclick=\"document.location.href='/internalarea/notedirectory/details/" . $row->id . "';\">";
			foreach ($columns as $columnName => $coumnTitle)
			{
				echo "<td>" . escapeText($row->{$columnName}) . "</td>";
			}
			echo "</tr>";
		}
		echo "</tbody>";
	}
}
?>