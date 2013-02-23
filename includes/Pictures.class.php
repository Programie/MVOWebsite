<?php
class Pictures
{
	public static function getAlbums($year)
	{
		$query = Constants::$pdo->prepare("SELECT * FROM `picturealbums` WHERE YEAR(`date`) = :year");
		$query->execute(array
		(
			":year" => $year
		));
		
		if (!$query->rowCount())
		{
			return null;
		}
		
		$albums = array();
		
		while ($row = $query->fetch())
		{
			$albums[] = $row;
		}
		
		return $albums;
	}
	
	public static function getPictures($year, $album)
	{
		$query = Constants::$pdo->prepare("SELECT `id`, `date`, `name`, `title` FROM `picturealbums` WHERE YEAR(`date`) = :year AND `name` = :name");
		$query->execute(array
		(
			":year" => $year,
			":name" => $album
		));
		
		if (!$query->rowCount())
		{
			return  null;
		}
		
		$albumRow = $query->fetch();
		
		$pictures = array();
		$path = "files/pictures/" . basename($year) . "/" . basename($albumRow->name);
		
		$dir = scandir(ROOT_PATH . "/" . $path);
		foreach ($dir as $file)
		{
			if (is_file($path . "/" . $file) and preg_match("/^img([0-9]+)/", $file, $fileParts))
			{
				$data = new StdClass;
				$data->file = $fileParts[1];
				$pictures[intval($fileParts[1])] = $data;
			}
		}
		
		$query = Constants::$pdo->prepare("SELECT * FROM `pictures` WHERE `albumId` = :albumId");
		$query->execute(array
		(
			":albumId" => $albumRow->id
		));
		while ($row = $query->fetch())
		{
			if (@$pictures[$row->number])
			{
				$row->file = $pictures[$row->number]->file;
				$pictures[$row->number] = $row;
			}
		}
		
		$data = new StdClass;
		$data->date = $albumRow->date;
		$data->pictures = $pictures;
		$data->title = $albumRow->title;
		
		return $data;
	}
	
	public static function getYears()
	{
		$query = Constants::$pdo->query("SELECT `picturealbums`.`year`, `pictureyears`.`coverAlbumId` FROM (SELECT YEAR(`date`) AS `year` FROM `picturealbums` GROUP BY `year`) AS `picturealbums` LEFT JOIN `pictureyears` ON `pictureyears`.`year` = `picturealbums`.`year`");
		
		if (!$query->rowCount())
		{
			return null;
		}
		
		$years = array();
		
		while ($row = $query->fetch())
		{
			$years[$row->year] = $row;
		}
		
		return $years;
	}
}
?>