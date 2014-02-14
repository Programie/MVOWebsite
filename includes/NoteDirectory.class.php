<?php
/**
 * Class to create the note directory HTML table
 */
class NoteDirectory
{
	/**
	 * @var Array An associated array containing the column names as keys and column titles as values
	 */
	private $columns;
	/**
	 * @var String The string which should be highlighted (e.g. for search results)
	 */
	private $highlightedString;
	/**
	 * @var Array An array containing the rows of the titles as maps. Each column in the table is one property in the row map.
	 */
	private $titles;
	/**
	 * @var Boolean Whether to show the titles in groups
	 */
	private $showInGroups;

	/**
	 * Create the body (Multiple tbody elements) of the table
	 */
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

	/**
	 * Create the header (Single thead element) of the table
	 */
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

	/**
	 * Create the table
	 */
	public function createList()
	{
		echo "
			<div class='alert-info no-print'>Klicke auf einen Titel um weitere Details anzuzeigen.</div>
			<table id='notedirectory_table' class='table {sortlist: [[0,0]]}'>
		";
		$this->createHeader();
		$this->createBody();
		echo "</table>";
	}

	/**
	 * Format the string using the formatText function and highlight the string specified in $highlightedString
	 *
	 * @param String $string The string which should be formatted
	 * @return String The formatted string
	 */
	public function formatString($string)
	{
		if ($this->highlightedString)
		{
			$string = preg_replace("/" . preg_quote($this->highlightedString, "/") . "/i", "[hl]\$0[/hl]", $string);
		}
		$string = formatText($string);

		return $string;
	}

	/**
	 * List all titles in categories
	 */
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

	/**
	 * List all titles without categories
	 * @param Array $titles An array containing the titles to show. Set to null or omit to show all titles.
	 */
	public function list_normal($titles = null)
	{
		if (!$titles)
		{
			$titles = $this->titles;
		}
		echo "<tbody>";
		foreach ($titles as $row)
		{
			echo "<tr class='pointer' onclick=\"document.location.href='/internalarea/notedirectory/details/" . $row->id . "';\">";
			foreach ($this->columns as $columnName => $columnTitle)
			{
				echo "<td columnname='" . $columnName . "'>" . $this->formatString($row->{$columnName}) . "</td>";
			}
			echo "</tr>";
		}
		echo "</tbody>";
	}

	/**
	 * Set the columns which should be shown
	 * @param Array $columns An associated array containing the column names as keys and column titles as values
	 */
	public function setColumns($columns)
	{
		$this->columns = $columns;
	}

	/**
	 * Set the string which should be highlighted in the table
	 * @param String $highlightedString The string which should be highlighted (e.g. for search results)
	 */
	public function setHighlight($highlightedString)
	{
		$this->highlightedString = $highlightedString;
	}

	/**
	 * Set the titles which should be shown in the table
	 * @param Array $titles An array containing the rows of the titles as maps. Each column in the table is one property in the row map.
	 */
	public function setTitles($titles)
	{
		$this->titles = $titles;
	}

	/**
	 * Set whether the titles should be shown in groups
	 * @param Boolean $showInGroups True to show in groups, false otherwise
	 */
	public function setShowInGroups($showInGroups)
	{
		$this->showInGroups = $showInGroups;
	}
}
?>