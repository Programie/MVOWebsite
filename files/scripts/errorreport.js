window.onerror = function(message, file, line)
{
	var postData = [];
	postData.push("message=" + encodeURIComponent(message));
	postData.push("file=" + encodeURIComponent(file));
	postData.push("line=" + encodeURIComponent(line));
	postData.push("url=" + encodeURIComponent(document.location.href));
	
	var xmlHttp = new XMLHttpRequest;
	xmlHttp.open("POST", "/jserror", true);
	xmlHttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlHttp.send(postData.join("&"));
	
	return true;
};