<?php
$calendar = Constants::$pagePath[1];
if ($calendar == "internal.ics" or $calendar == "public.ics")
{
	if ($calendar == "internal.ics")
	{
		if (Constants::$pagePath[2])
		{
			$query = Constants::$pdo->prepare("SELECT `id` FROM `users` WHERE `enabled` AND `calendarToken` = :calendarToken");
			$query->execute(array
			(
				":calendarToken" => Constants::$pagePath[2]
			));
			$row = $query->fetch();
			if ($row->id)
			{
				Constants::$accountManager->loginWithUserId($row->id);
			}
		}
		if (!Constants::$accountManager->getUserId())
		{
			header("WWW-Authenticate: Basic realm='Interner Bereich'");
			header("HTTP/1.0 401 Unauthorized");
			exit;
		}
	}
	
	header("Content-Type: text/calendar");
	
	require_once ROOT_PATH . "/includes/icalendar/iCalWriter.php";
	
	$calendar = new iCalWriter;
	$calendar->setDownloadOutput();
	$calendar->start();
	
	$dates = Dates::getDates();
	foreach ($dates as $date)
	{
		$event = new iCalEvent;
		
		if (date("H:i:s", $date->startDate) == "00:00:00")
		{
			$useStartTime = false;
		}
		else
		{
			$useStartTime = true;
		}
		$event->setStart(date("Y", $date->startDate), date("m", $date->startDate), date("d", $date->startDate), false, true, "Europe/Berlin", $useStartTime, date("H", $date->startDate), date("i", $date->startDate), date("s", $date->startDate));
		
		if (!$date->endDate)
		{
			$date->endDate = $date->startDate;
		}
		
		if (date("H:i:s", $date->endDate) == "00:00:00")
		{
			$date->endDate += 60 * 60 * 24;// End date specifies the non-inclusive end of the event
			$useEndTime = false;
		}
		else
		{
			$useEndTime = true;
		}
		$event->setEnd(date("Y", $date->endDate), date("m", $date->endDate), date("d", $date->endDate), false, true, "Europe/Berlin", $useEndTime, date("H", $date->endDate), date("i", $date->endDate), date("s", $date->endDate));
		$event->setShortDescription($date->title);
		$event->setLongDescription($date->description);
		$event->setLocation($date->location->name);
		if ($date->location->latitude and $date->location->longitude)
		{
			$event->setGeo($date->location->latitude, $date->location->longitude);
		}
		$event->setUID($row->id . "-" . $date->startDate . "@dates." . $_SERVER["SERVER_NAME"]);
		$calendar->add($event);
	}
	
	$calendar->end();
	exit;
}

if (substr(Constants::$pagePath[1], -4) == ".pdf")
{
	require_once ROOT_PATH . "/includes/pdf/FPDF_ExtendedTables.class.php";
	
	$year = Constants::$pagePath[1];
	$year = substr($year, 0, strlen($year) - 4);
	
	$year = Dates::convertYear($year);
	
	$containingGroups = array();
	
	$dates = Dates::getDates($year, explode(" ", Constants::$pagePath[2]), $containingGroups);
	if ($dates)
	{
		$title = "Termine";
		$yearText = Dates::getYearText($year);
		if ($yearText)
		{
			$title .= " - " . $yearText;
		}
		
		$pdf = new FPDF_ExtendedTables();
		
		$groupTitles = "";
		if (Constants::$accountManager->getUserId() and !empty($containingGroups))
		{
			$groupTitles = array();
			$query = Constants::$pdo->query("SELECT `name`, `title` FROM `usergroups` ORDER BY `title` ASC");
			while ($row = $query->fetch())
			{
				if (in_array($row->name, $containingGroups))
				{
					$groupTitles[] = utf8_decode($row->title);
				}
			}
			$groupTitles = implode(", ", $groupTitles);
			$pdf->SetSubject($groupTitles);
		}
		
		$contentWidth = $pdf->w - $pdf->lMargin - $pdf->rMargin;
		
		$header = array
		(
			array
			(
				"width" => $contentWidth * 0.15,
				"text" => "Datum"
			),
			array
			(
				"width" => $contentWidth * 0.15,
				"text" => "Zeit"
			),
			array
			(
				"width" => $contentWidth * 0.4,
				"text" => "Veranstaltung"
			),
			array
			(
				"width" => $contentWidth * 0.3,
				"text" => "Ort"
			)
		);
		
		$headerCells = array();
		foreach ($header as $column => $cell)
		{
			if ($column)
			{
				$lineArea = "L";
			}
			else
			{
				$lineArea = "";
			}
			$headerCells[] = array
			(
				"width" => $cell["width"],
				"height" => 10,
				"text" => $cell["text"],
				"align" => "C",
				"lineArea" => $lineArea
			);
		}
		
		$callbackData = array
		(
			"groupTitles" => $groupTitles,
			"tableHeaders" => array($headerCells),
			"title" => $title
		);
		
		$pdf->SetHeaderCallback(function($classInstance, $callbackData)
		{
			$classInstance->SetFont("Arial", "", 20);
			$classInstance->Cell(0, 10, $callbackData["title"], 0, 1);
			
			$classInstance->SetFont("Arial", "", 10);
			
			if ($callbackData["groupTitles"])
			{
				$classInstance->Cell(0, 10, $callbackData["groupTitles"], 0, 1);
			}
			
			$classInstance->Cell(0, 10, "Stand: " . date("d.m.Y"), 0, 1);
			
			$classInstance->WriteTable($callbackData["tableHeaders"]);
		}, $callbackData);
		
		$pdf->SetAuthor("Musikverein \"Orgelfels\" Reichental e.V.");
		$pdf->SetTitle($title);
		
		$pdf->SetFont("Arial", "", 10);
		$pdf->SetDrawColor(128, 128, 128);
		
		$pdf->AddPage();
		
		$data = array();
		
		foreach ($dates as $row)
		{
			$description = array(trim($row->title));
			if ($row->description)
			{
				$description[] = trim($row->description);
			}
			$row = array
			(
				Dates::getDateText($row->startDate),
				Dates::getTimeText($row->startDate, $row->endDate),
				utf8_decode(implode("\n", $description)),
				utf8_decode($row->location->name)
			);
			
			$cells = array();
			foreach ($row as $column => $cell)
			{
				if ($column)
				{
					$lineArea = "LT";
				}
				else
				{
					$lineArea = "T";
				}
				$cells[] = array
				(
					"width" => $header[$column]["width"],
					"height" => 7,
					"text" => $cell,
					"align" => "L",
					"lineArea" => $lineArea
				);
			}
			$data[] = $cells;
		}
		
		$pdf->WriteTable($data);
		
		$pdf->Output($title . ".pdf", "I");
	}
	else
	{
		header("HTTP/1.1 404 Not Found");
		echo "<h1>Keine Termine vorhanden</h1>";
	}
	exit;
}
?>