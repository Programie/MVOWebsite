$(document).ready(function()
{
	// Colorbox
	$(".colorbox-iframe").colorbox(
	{
		iframe : true,
		width : "80%",
		height : "80%"
	});
	$("[rel='colorbox']").colorbox(
	{
		current : "",
		picture : true
	});
	
	// "Back to top" link
	$("#backtotop").css("display", "none");
	$("#backtotop").click(function()
	{
		$("body,html").animate(
		{
			scrollTop : 0
		}, 800);
	});
	
	// Menu
	$(".menu").menu();
	
	// Tablesorter
	$("table.table").tablesorter(
	{
		widgets : ["stickyHeaders"]
	});
});

$(window).scroll(function()
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
});

$(window).resize(function()
{
	//fixSize();
});

$.tablesorter.addParser(
{
	id : "number-attribute",
	is : function(string)
	{
		return false;
	},
	format : function(string, table, cell, cellIndex)
	{
		return $(cell).attr("number");
	},
	type : "numeric"
});

function fixSize()
{
	$("#backgroundoverlay").css("top", (document.getElementById("backgroundimage").offsetHeight - 500) + "px");
	$("#backgroundwrapper").css("height", document.getElementById("container").offsetHeight + "px");
	if (document.getElementById("container").offsetHeight > document.getElementById("backgroundimage").offsetHeight)
	{
		document.getElementById("backgroundoverlay").style.display = "block";
	}
	else
	{
		document.getElementById("backgroundoverlay").style.display = "none";
	}
}