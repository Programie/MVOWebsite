<?php
if (!Constants::$pagePath[1])
{
	if (Constants::$accountManager->getUserId())
	{
		Constants::$pagePath[1] = "home";
	}
	else
	{
		Constants::$pagePath[1] = "login";
	}
}

include getValidContentFile(Constants::$pagePath[0] . "/" . Constants::$pagePath[1], false);