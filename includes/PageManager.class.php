<?php
class PageManager
{
	private $structure;
	
	public function __construct($structure)
	{
		$this->structure = $structure;
	}
	
	public function getPageData($pagePathArray)
	{
		$structure = $this->structure;
		
		$pages = array();
		
		foreach ($pagePathArray as $pagePathPart)
		{
			$item = @$structure->{$pagePathPart};
			if (!$item)
			{
				break;
			}
			
			$newItem = clone $item;
			unset($newItem->subpages);
			
			if ($item->permissions)
			{
				if (is_array($item->permissions))
				{
					foreach ($item->permissions as $permission)
					{
						if (Constants::$accountManager->hasPermission($permission))
						{
							$newItem->hasPermission = true;
							break;
						}
					}
				}
				else
				{
					if (Constants::$accountManager->hasPermission($item->permissions))
					{
						$newItem->hasPermission = true;
						break;
					}
				}
			}
			else
			{
				$newItem->hasPermission = true;
			}
			
			$pages[] = $newItem;
			
			$structure = @$item->subpages;
			if (!$structure)
			{
				break;
			}
		}
		
		return $pages;
	}
}
?>