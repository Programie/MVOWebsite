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
				$newItem->hasPermission = Constants::$accountManager->hasPermission($item->permissions);
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
	
	public function includePage($pageIndex)
	{
		$path = array_slice(Constants::$pagePath, 0, $pageIndex + 1);
		
		$pageData = $this->getPageData($path);
		
		if (count(Constants::$pagePath) == count($path) and $pageIndex <= count($pageData) - 1)
		{
			$indexPage = $pageData[count($pageData) - 1]->index;
			if ($indexPage)
			{
				Constants::$pagePath[$pageIndex + 1] = $indexPage;
				return $this->includePage($pageIndex + 1);
			}
		}
		
		foreach ($pageData as $data)
		{
			if (!$data->hasPermission)
			{
				setAdditionalHeader("HTTP/1.1 403 Forbidden");
				require_once ROOT_PATH . "/includes/html/forbidden.php";
				return false;
			}
		}
		
		$path = ROOT_PATH . "/content/" . implode("/", $path);
		
		// home.php or subpage/mypage.php
		$fullPath = $path . ".php";
		if (file_exists($fullPath))
		{
			require_once $fullPath;
			return true;
		}
		
		// home/index.php or subpage/mypage/index.php
		$fullPath = $path . "/index.php";
		if (file_exists($fullPath))
		{
			require_once $fullPath;
			return true;
		}

		setAdditionalHeader("HTTP/1.1 404 Not Found");
		require_once ROOT_PATH . "/includes/html/errorpage.php";
		return false;
	}
}
?>