<?php
/**
 * Class used to get pictures from the database
 */
class Pictures
{
	/**
	 * Get all albums of the specified year
	 * @param int $year The year of which the albums should be retrieved
	 * @return array|null An array containing the rows of all found albums which the logged in user is able to see, null if there are no albums, an empty array if the user is unable to access any found album
	 */
	public static function getAlbums($year)
	{
		$query = Constants::$pdo->prepare("SELECT * FROM `picturealbums` WHERE YEAR(`date`) = :year ORDER BY `date` DESC");
		$query->execute(array(":year" => $year));

		if (!$query->rowCount())
		{
			return null;
		}

		$albums = array();

		while ($row = $query->fetch())
		{
			if (!Constants::$accountManager->hasPermission($row->permission))
			{
				continue;
			}

			$albums[] = $row;
		}

		return $albums;
	}

	/**
	 * Get all pictures of the specified year and the specified album
	 * @param int $year The year of the album
	 * @param string $album The name of the album of which the pictures should be retrieved
	 * @return null|StdClass A map containing the following properties:
	 * <ul>
	 *  <li>date: The date of the album in ISO format (YYYY-MM-DD)</li>
	 *  <li>pictures: An associated array containing an element for each picture in the album which contains additional information (Picture number is the key)</li>
	 *  <li>text: The description of the album</li>
	 *  <li>title: The title of the album</li>
	 * </ul>
	 * null is returned if the album does not exist or the user does not have required permission
	 */
	public static function getPictures($year, $album)
	{
		$query = Constants::$pdo->prepare("SELECT `id`, `date`, `permission`, `name`, `title`, `text` FROM `picturealbums` WHERE YEAR(`date`) = :year AND `name` = :name");
		$query->execute(array(":year" => $year, ":name" => $album));

		if (!$query->rowCount())
		{
			return null;
		}

		$albumRow = $query->fetch();

		if (!Constants::$accountManager->hasPermission($albumRow->permission))
		{
			return null;
		}

		$pictures = array();
		$path = "files/pictures/" . basename($year) . "/" . basename($albumRow->name);

		$dir = scandir(ROOT_PATH . "/" . $path);
		foreach ($dir as $file)
		{
			if (is_file($path . "/" . $file) and preg_match("/^large([0-9]+)/", $file, $fileParts))
			{
				$data = new StdClass;
				$data->file = $fileParts[1];
				$pictures[intval($fileParts[1])] = $data;
			}
		}

		$query = Constants::$pdo->prepare("SELECT * FROM `pictures` WHERE `albumId` = :albumId");
		$query->execute(array(":albumId" => $albumRow->id));
		while ($row = $query->fetch())
		{
			if (@$pictures[$row->number])
			{
				$row->file = $pictures[$row->number]->file;
				$pictures[$row->number] = $row;
			}
		}

		ksort($pictures);

		$data = new StdClass;
		$data->date = $albumRow->date;
		$data->pictures = $pictures;
		$data->text = $albumRow->text;
		$data->title = $albumRow->title;

		return $data;
	}

	/**
	 * Get all available years
	 * @return array|null An associated array (Key is the year, value is a map containing additional information)
	 * The maps in each element contain the following properties:
	 * <ul>
	 *  <li>year: The year (YYYY)</li>
	 *  <li>coverAlbumId: The album ID which should be used as the cover of the year</li>
	 * </ul>
	 * null is returned if there are no albums
	 */
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