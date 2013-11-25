var _gaq = _gaq || [];
_gaq.push(["_setAccount", "UA-26151313-1"]);
_gaq.push(["_trackPageview"]);

(function()
{
	var googleAnalyticsScriptElement = document.createElement("script");
	googleAnalyticsScriptElement.type = "text/javascript";
	googleAnalyticsScriptElement.async = true;
	googleAnalyticsScriptElement.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";
	var scriptElement = document.getElementsByTagName("script")[0];
	scriptElement.parentNode.insertBefore(googleAnalyticsScriptElement, scriptElement);
})();