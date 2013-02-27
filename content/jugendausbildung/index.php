<?php
if (!Constants::$pagePath[1])
{
	Constants::$pagePath[1] = "verein";
}
include getValidContentFile(Constants::$pagePath[0] . "/" . Constants::$pagePath[1], false);
?>