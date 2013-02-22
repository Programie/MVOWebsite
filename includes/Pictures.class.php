<?php
class Pictures
{
	public function getAlbums($year)
	{
		$albums = array();
		
		$query = Constants::$pdo->prepare("SELECT * FROM `picturealbums` WHERE YEAR(`date`) = :year");
		$query->execute(array
		(
			":year" => $year
		));
		while ($row = $query->fetch())
		{
			$albums[] = $row;
		}
		
		return $albums;
	}
	
	public function getYears()
	{
		$years = array();
		
		$query = Constants::$pdo->query("SELECT YEAR(`date`) AS `year` FROM `picturealbums` GROUP BY `year`");
		while ($row = $query->fetch())
		{
			$years[] = $row->year;
		}
		
		return $years;
	}
}
?>