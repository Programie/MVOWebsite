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
		if (is_array($items))
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
					if (!is_array($item->items))
					{
						$item->items = $this->getSpecialMenu($item->items);
					}
					$this->addMenu($item->items);
				}
				echo "</li>";
			}
			echo "</ul>";
		}
	}
	
	private function getSpecialMenu($name)
	{
		switch ($name)
		{
			case "dates":
				break;
			case "internarea":
				break;
			case "pictures":
				$items = array();
				
				$instance = new Pictures;
				
				$years = $instance->getYears();
				krsort($years, SORT_NUMERIC);
				foreach ($years as $year => $data)
				{
					// BUG: Too many albums result in scrolling page
					/*$albumItems = array();
					$albums = $instance->getAlbums($year);
					foreach ($albums as $album)
					{
						$albumItem = new StdClass;
						$albumItem->path = "/pictures/" . $year . "/" . $album->name;
						$albumItem->title = $album->title;
						$albumItems[] = $albumItem;
					}*/
					$yearItem = new StdClass;
					$yearItem->path = "/pictures/" . $year;
					$yearItem->title = $year;
					//$yearItem->items = $albumItems;
					$items[] = $yearItem;
				}
				
				return $items;
		}
	}
}
?>