<?php
$yearFound = false;
if (Constants::$pagePath[1])// Year
{
	$albumFound = false;
	if (Constants::$pagePath[2])// Album
	{
		$albumData = Pictures::getPictures(Constants::$pagePath[1], Constants::$pagePath[2]);
		
		if ($albumData)
		{
			$albumFound = true;
			$yearFound = true;
			$path = "files/pictures/" . basename(Constants::$pagePath[1]) . "/" . basename(Constants::$pagePath[2]);
			$pictures = $albumData->pictures;
			
			echo "<h1>" . $albumData->title . "</h1>";
			echo "<p>Datum: " . date("d.m.Y", strtotime($albumData->date)) . "</p>";
			
			if ($albumData->text)
			{
				echo "<p id='pictures_text'>" . formatText($albumData->text) . "</p>";
			}
			
			echo "<ul id='gallery' class='polaroids'>";
			foreach ($pictures as $number => $data)
			{
				echo "
					<li>
						<a href='/" . $path . "/large" . $data->file . ".jpg' caption='" . $data->text . "'>
							<img src='/" . $path . "/small" . $data->file . ".jpg' alt='" . ($data->text ? $data->text : " ") . "'/>
						</a>
					</li>
				";
			}
			echo "
				</ul>
				
				<div class='clear'></div>
				
				<script type='text/javascript'>
					$('#gallery').photobox('li > a',
					{
						history : false,
						loop : false,
						time : 10000
					});
				</script>
			";
		}
	}
	
	if (!$albumFound)
	{
		$albums = Pictures::getAlbums(Constants::$pagePath[1]);
		
		if ($albums)
		{
			$yearFound = true;
			
			echo "<h1>Fotogalerie von " . Constants::$pagePath[1] . "</h1>";
			
			echo "<ul class='polaroids'>";
			foreach ($albums as $data)
			{
				echo "
					<li>
						<a href='/pictures/" . Constants::$pagePath[1] . "/" . $data->name . "' caption='" . $data->title . "'>
							<img src='/files/pictures/" . Constants::$pagePath[1] . "/" . $data->name . "/small" . str_pad($data->coverPicture, 3, "0", STR_PAD_LEFT) . ".jpg'/>
						</a>
					</li>
				";
			}
			echo "</ul>";
			
			echo "<div class='clear'></div>";
		}
	}
}
if (!$yearFound)
{
	echo "<h1>Fotogalerie</h1>";
	
	$years = Pictures::getYears();
	krsort($years, SORT_NUMERIC);
	
	$queryId = Constants::$pdo->prepare("SELECT `name`, `coverPicture` FROM `picturealbums` WHERE `id` = :id");
	$queryYear = Constants::$pdo->prepare("SELECT `name`, `coverPicture` FROM `picturealbums` WHERE YEAR(`date`) = :year ORDER BY `id` ASC LIMIT 1");
	
	echo "<ul class='polaroids'>";
	foreach ($years as $year => $data)
	{
		$queryId->execute(array
		(
			":id" => $data->coverAlbumId
		));
		if ($queryId->rowCount())
		{
			$query = $queryId;
		}
		else
		{
			$queryYear->execute(array
			(
				":year" => $year
			));
			$query = $queryYear;
		}
		$albumRow = $query->fetch();
		echo "
			<li>
				<a href='/pictures/" . $year . "' caption='" . $year . "'>
					<img src='/files/pictures/" . $year . "/" . $albumRow->name . "/small" . str_pad($albumRow->coverPicture, 3, "0", STR_PAD_LEFT) . ".jpg'/>
				</a>
			</li>
		";
	}
	echo "</ul>";
	
	echo "<div class='clear'></div>";
}
?>