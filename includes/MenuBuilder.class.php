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
			$firstItem = true;
			foreach ($items as $item)
			{
				$pagesData = null;
				if ($item->path[0] == "/")
				{
					$hasPermission = true;
					
					$pagesData = Constants::$pageManager->getPageData(explode("/", $item->path));
					foreach ($pagesData as $pageData)
					{
						if (!$pageData->hasPermission)
						{
							$hasPermission = false;
							break;
						}
					}
					
					if (!$hasPermission)
					{
						continue;
					}
				}
				
				if ($item->permissions)
				{
					if (!Constants::$accountManager->hasPermission($item->permissions))
					{
						continue;
					}
				}
				
				if ($firstItem)
				{
					$firstItem = false;
					echo "<ul>";
				}
				
				if ($item->type == "separator")
				{
					echo "<li class='menu_separator'></li>";
					continue;
				}
				
				$title = $item->title;
				if (!$title and is_array($pagesData))
				{
					$title = $pagesData[count($pagesData) - 1]->title;
				}
				
				echo "<li>";
				if ($item->path)
				{
					echo "<a href=\"" . $item->path . "\">" . $title . "</a>";
				}
				else
				{
					echo "<span>" . $title . "</span>";
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
			if (!$firstItem)
			{
				echo "</ul>";
			}
		}
	}
	
	private function getSpecialMenu($name)
	{
		switch ($name)
		{
			case "dates":
				$items = array();
				
				$years = Dates::getYears();
				krsort($years, SORT_NUMERIC);
				foreach ($years as $year => $data)
				{
					$yearItem = new StdClass;
					$yearItem->path = "/dates/" . $year;
					$yearItem->title = $year;
					$items[] = $yearItem;
				}
				
				return $items;
			case "pictures":
				$items = array();
				
				$years = Pictures::getYears();
				krsort($years, SORT_NUMERIC);
				foreach ($years as $year => $data)
				{
					$yearItem = new StdClass;
					$yearItem->path = "/pictures/" . $year;
					$yearItem->title = $year;
					$items[] = $yearItem;
				}
				
				return $items;
		}
	}
}
?>