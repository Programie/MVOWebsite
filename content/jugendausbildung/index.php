<?php
if (Constants::$getPageTitle)
{
	$title =  "Jugendausbildung";
	return;
}
if (!Constants::$pagePath[1])
{
	Constants::$pagePath[1] = "groups";
}
include getValidContentFile(Constants::$pagePath[0] . "/" . Constants::$pagePath[1], false);
?>