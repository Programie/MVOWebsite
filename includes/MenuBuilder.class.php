<?php
class MenuBuilder
{
	public function __construct($items)
	{
		echo "<nav>";
		$this->addMenu($items);
		echo "</nav>";
	}
	
	private function addMenu($items)
	{
		echo "<ul>";
		foreach ($items as $item)
		{
			echo "<li>";
			if ($item->path)
			{
				echo "<a href=\"" . $item->path . "\">" . $item->title . "</a>";
			}
			else
			{
				echo "<span>" . $item->title . "</span>";
			}
			if ($item->items)
			{
				if (is_array($item->items))
				{
					$this->addMenu($item->items);
				}
				else
				{
					$this->addSpecialMenu($item->items);
				}
			}
			echo "</li>";
		}
		echo "</ul>";
	}
	
	private function addSpecialMenu($name)
	{
	}
}
?>