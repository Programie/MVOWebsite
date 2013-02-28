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
			if (!$pagePathPart)
			{
				continue;
			}
			
			$item = @$structure->{$pagePathPart};
			if (!$item)
			{
				break;
			}
			
			$newItem = clone $item;
			unset($newItem->subpages);
			
			if ($item->permissions == null)
			{
				$newItem->hasPermission = true;
			}
			else
			{
				if (is_array($item->permissions))
				{
					$newItem->hasPermission = Constants::$accountManager->hasPermissionInArray($item->permissions);
				}
				else
				{
					$newItem->hasPermission = Constants::$accountManager->hasPermission($item->permissions);
				}
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