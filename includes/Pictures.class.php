<?php
/**
 * Class used to get pictures from the database
 */
class Pictures
{
	/**
	 * Get all albums of the specified year
	 * @param int $year The year of which the albums should be retrieved
	 * @return array An array containing the rows of all found albums which the user can see
	 */
	public static function getAlbums($year)
	{
		$query = Constants::$pdo->prepare("
			SELECT
				`picturealbums`.`id`,
				`published`,
				`isPublic`,
				`title`,
				`fileId` AS `coverPictureFileId`
			FROM `picturealbums`
			LEFT JOIN `pictures` ON `pictures`.`albumId` = `picturealbums`.`id` AND `pictures`.`number` = `picturealbums`.`coverPicture`
			WHERE YEAR(`date`) = :year
			ORDER BY `date` DESC
		");
		$query->execute(array(":year" => $year));

		$albums = array();

		while ($row = $query->fetch())
		{
			if (!$row->published and !Constants::$accountManager->hasPermission("pictures.view.unpublished"))
			{
				continue;
			}

			if (!$row->isPublic and !Constants::$accountManager->getUserId())
			{
				continue;
			}

			$albums[] = $row;
		}

		return $albums;
	}

	/**
	 * Get all pictures of the specified year and the specified album
	 * @param int $albumId The ID of the album
	 * @return null|StdClass A map containing the following properties:
	 * <ul>
	 *  <li>published: true if the album has been published, false if not</li>
	 *  <li>date: The date of the album in ISO format (YYYY-MM-DD)</li>
	 *  <li>
	 *   pictures: An array containing an element of type StdClass for each picture in the album.
	 *   The elements contain the following properties:
	 *   <ul>
	 *    <li>fileId: The ID of the file (File names are large_<fileId>.jpg and small_<fileId>.jpg)</li>
	 *    <li>text: An additional text for the picture</li>
	 *   </ul>
	 *  </li>
	 *  <li>text: The description of the album</li>
	 *  <li>title: The title of the album</li>
	 * </ul>
	 * null is returned if the album does not exist or the user does not have the permission to see the album (e.g. album not published or user not logged in)
	 */
	public static function getPictures($albumId)
	{
		$query = Constants::$pdo->prepare("SELECT `published`, `date`, `isPublic`, `title`, `text` FROM `picturealbums` WHERE `id` = :id");
		$query->execute(array(":id" => $albumId));

		if (!$query->rowCount())
		{
			return null;
		}

		$albumRow = $query->fetch();

		if (!$albumRow->published and !Constants::$accountManager->hasPermission("pictures.view.unpublished"))
		{
			return null;
		}

		if (!$albumRow->isPublic and !Constants::$accountManager->getUserId())
		{
			return null;
		}

		$query = Constants::$pdo->prepare("SELECT `fileId`, `number`, `text` FROM `pictures` WHERE `albumId` = :albumId ORDER BY `number` ASC");
		$query->execute(array(":albumId" => $albumId));

		if (!$query->rowCount())
		{
			return null;
		}

		$data = new StdClass;
		$data->published = $albumRow->published;
		$data->date = $albumRow->date;
		$data->pictures = $query->fetchAll();
		$data->text = $albumRow->text;
		$data->title = $albumRow->title;

		return $data;
	}

	/**
	 * Get all available years
	 * @return array An associated array (Key is the year, value is a map containing additional information)
	 * The maps in each element contain the following properties:
	 * <ul>
	 *  <li>year: The year (YYYY)</li>
	 *  <li>coverAlbumId: The album ID which should be used as the cover of the year</li>
	 * </ul>
	 */
	public static function getYears()
	{
		$whereSql = "";
		if (!Constants::$accountManager->hasPermission("pictures.view.unpublished"))
		{
			$whereSql = "WHERE `published`";
		}

		$query = Constants::$pdo->query("
			SELECT `picturealbums`.`year`, `pictureyears`.`coverAlbumId`
			FROM (SELECT YEAR(`date`) AS `year` FROM `picturealbums` " . $whereSql . " GROUP BY `year`) AS `picturealbums`
			LEFT JOIN `pictureyears` ON `pictureyears`.`year` = `picturealbums`.`year`
		");

		$years = array();

		while ($row = $query->fetch())
		{
			$years[$row->year] = $row;
		}

		return $years;
	}

	/**
	 * Update the album of the year
	 *
	 * @param int $year The year for which the album should be set
	 * @param int $albumId The ID of the album
	 * @param bool $set true to set this album as the album of the year, false to only set this album if there is not already a valid album of the year
	 */
	public static function updateAlbumOfTheYear($year, $albumId, $set)
	{
		if (!$set)
		{
			$query = Constants::$pdo->prepare("
				SELECT `picturealbums`.`id`
				FROM `pictureyears`
				LEFT JOIN `picturealbums` ON `picturealbums`.`id` = `pictureyears`.`coverAlbumId`
				WHERE `year` = :year
			");
			$query->execute(array
			(
				":year" => $year
			));
			$row = $query->fetch();
			if (@!$row->id)
			{
				$set = true;
			}
		}

		if ($set)
		{
			$query = Constants::$pdo->prepare("INSERT INTO `pictureyears` (`year`, `coverAlbumId`) VALUES(:year, :coverAlbumId) ON DUPLICATE KEY UPDATE `coverAlbumId` = :coverAlbumId");
			$query->execute(array
			(
				":year" => $year,
				":coverAlbumId" => $albumId
			));
		}
	}
}
?>