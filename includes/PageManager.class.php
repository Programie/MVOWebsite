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