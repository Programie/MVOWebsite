<?php
$calendar = Constants::$pagePath[1];
if ($calendar == "internal.ics" or $calendar == "public.ics")
{
	if ($calendar == "internal.ics")
	{
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
		
		if (date("Y-m-d", $date->endDate) == "1970-01-01")
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
?>