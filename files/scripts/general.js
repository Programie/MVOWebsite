$(document).ready(function()
{
	// Colorbox
	$(".colorbox-iframe").colorbox(
	{
		iframe : true,
		returnFocus : false,
		width : "80%",
		height : "80%"
	});
	$("[rel='colorbox']").colorbox(
	{
		current : "",
		picture : true,
		returnFocus : false
	});
	
	// "Back to top" button
	$("#backtotop").css("display", "none");
	$("#backtotop").click(function()
	{
		$("body,html").animate(
		{
			scrollTop : 0
		}, 2000, "easeOutExpo");
	});
	
	// Print button
	$("#print").click(function()
	{
		window.print();
	});
	
	// jQuery UI
	$("button, input[type=submit]").button();
	$(".date").datepicker(
	{
		changeMonth : true,
		changeYear : true,
		dateFormat : "dd.mm.yy",
		dayNames : ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"],
		dayNamesMin : ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"],
		firstDay : 1,
		monthNames : ["Januar", "Februar", "M&auml;rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"],
		monthNamesShort : ["Jan", "Feb", "M&auml;rz", "April", "Mai", "Juni", "Juli", "Aug", "Sep", "Okt", "Nov", "Dez"],
		showAnim : "slideDown"
	});
	$(".menu").menu();
	$(document).tooltip(
	{
		track : true
	});
	
	// Tablesorter
	$("table.table").tablesorter(
	{
		widgets : ["stickyHeaders"]
	});
	
	$.noty.defaults.layout = "bottom";
	$.noty.defaults.timeout = 10000;
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

$.tablesorter.addParser(
{
	id : "text-attribute",
	is : function(string)
	{
		return false;
	},
	format : function(string, table, cell, cellIndex)
	{
		return $(cell).attr("sorttext");
	},
	type : "text"
});