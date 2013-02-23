$(function()
{
	$(window).load(function()
	{
		updateBackToTop();
	});
	
	$(window).scroll(function()
	{
		updateBackToTop();
	});
	
	$("#backtotop").click(function()
	{
		$("body,html").animate(
		{
			scrollTop : 0
		},800);
	});
});

$(window).resize(function()
{
	fixSize();
});

window.onload = fixSize;

function updateBackToTop(direct)
{
	var div = $("#backtotop");
	if ($(window).scrollTop() > 0)
	{
		div.fadeIn();
	}
	else
	{
		div.fadeOut();
	}
}

function fixSize()
{
	document.getElementById("backgroundoverlay").style.top = (document.getElementById("backgroundimage").offsetHeight - 500) + "px";
	document.getElementById("backgroundwrapper").style.height = document.getElementById("container").offsetHeight + "px";
	if (document.getElementById("container").offsetHeight > document.getElementById("backgroundimage").offsetHeight)
	{
		document.getElementById("backgroundoverlay").style.display = "block";
	}
	else
	{
		document.getElementById("backgroundoverlay").style.display = "none";
	}
}