<?php
if (!Constants::$pagePath[1])
{
	Constants::$pagePath[1] = "why";
}
include getValidContentFile(Constants::$pagePath[0] . "/" . Constants::$pagePath[1], false);
?>