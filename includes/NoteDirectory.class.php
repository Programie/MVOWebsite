<?php
class NoteDirectory
{
	private $columns;
	private $highlightedString;
	private $titles;
	private $showInGroups;
	
	public function createBody()
	{
		if ($this->showInGroups)
		{
			$this->list_categories();
		}
		else
		{
			$this->list_normal();
		}
	}
	
	public function createHeader()
	{
		echo "
			<thead>
				<tr>
		";
		foreach ($this->columns as $columnTitle)
		{
			echo "<th>" . escapeText($columnTitle) . "</th>";
		}
		echo "
				</tr>
			</thead>
		";
	}
	
	public function createList()
	{
		echo "
			<div class='info no-print'>Klicke auf einen Titel um weitere Details anzuzeigen.</div>
			<table id='notedirectory_table' class='table {sortlist: [[0,0]]}'>
		";
		$this->createHeader();
		$this->createBody();
		echo "</table>";
	}
	
	public function formatString($string)
	{
		if ($this->highlightedString)
		{
			$string = preg_replace("/" . preg_quote($this->highlightedString, "/") . "/i", "[hl]\$0[/hl]", $string);
		}
		$string = formatText($string);
		return $string;
	}
	
	public function list_categories()
	{
		$categories = array();
		foreach ($this->titles as $row)
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
						<th colspan='" . count($this->columns) . "'>" . $this->formatString($category) . "</th>
					</tr>
				</tbody>
			";
			$this->list_normal($titles);
		}
	}
	
	public function list_normal($titles = null)
	{
		if (!$titles)
		{
			$titles = $this->titles;
		}
		echo "<tbody>";
		foreach ($titles as $index => $row)
		{
			echo "<tr class='pointer' onclick=\"document.location.href='/internalarea/notedirectory/details/" . $row->id . "';\">";
			foreach ($this->columns as $columnName => $coumnTitle)
			{
				echo "<td>" . $this->formatString($row->{$columnName}) . "</td>";
			}
			echo "</tr>";
		}
		echo "</tbody>";
	}
	
	public function setColumns($columns)
	{
		$this->columns = $columns;
	}
	
	public function setHighlight($highlightedString)
	{
		$this->highlightedString = $highlightedString;
	}
	
	public function setTitles($titles)
	{
		$this->titles = $titles;
	}
	
	public function setShowInGroups($showInGroups)
	{
		$this->showInGroups = $showInGroups;
	}
}
?>