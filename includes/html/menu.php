<?php
$navbarData = json_decode(file_get_contents(ROOT_PATH . "/includes/menu.json"));

$mustache = new Mustache_Engine();

foreach ($navbarData->menus as &$menu)
{
	$pagesData = Constants::$pageManager->getPageData(explode("/", $menu->path));

	if (!$menu->title)
	{
		$menu->title = end($pagesData)->title;
	}

	if (is_array($menu->items))
	{
		foreach ($menu->items as $itemIndex => $item)
		{
			$pagesData = Constants::$pageManager->getPageData(explode("/", $item));

			$hasPermission = true;

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
				unset($menu->items[$itemIndex]);
				continue;
			}

			$itemData = new StdClass;

			if ($item == "separator")
			{
				$itemData->separator = true;
			}

			$itemData->path = $item;
			$itemData->title = end($pagesData)->title;

			$menu->items[$itemIndex] = $itemData;
		}

		$menu->items = array_values($menu->items);
	}
	else
	{
		switch ($menu->items)
		{
			case "dates":
				$items = array();

				$item = new StdClass;
				$item->path = "/dates/current";
				$item->title = "Aktuell";
				$items[] = $item;

				$years = Dates::getYears();
				krsort($years, SORT_NUMERIC);
				foreach ($years as $year => $data)
				{
					$yearItem = new StdClass;
					$yearItem->path = "/dates/" . $year;
					$yearItem->title = $year;
					$items[] = $yearItem;
				}

				$menu->items = $items;
				break;
			case "pictures":
				$items = array();

				$years = Pictures::getYears();
				krsort($years, SORT_NUMERIC);
				foreach ($years as $year => $data)
				{
					$yearItem = new StdClass;
					$yearItem->path = "/pictures/year/" . $year;
					$yearItem->title = $year;
					$items[] = $yearItem;
				}

				$menu->items = $items;
				break;
		}
	}
}

echo $mustache->render(file_get_contents(__DIR__ . "/../templates/menu.html"), $navbarData);
?>