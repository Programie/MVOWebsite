$(function()
{
	$(window).scroll(function()
	{
		if ($(this).scrollTop() != 0)
		{
			$('#backtotop').fadeIn();
		}
		else
		{
			$('#backtotop').fadeOut();
		}
	});
 
	$('#backtotop').click(function()
	{
		$('body,html').animate({scrollTop:0},800);
	});
});

$(window).resize(function()
{
	fixSize();
});

window.onload = fixSize;

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