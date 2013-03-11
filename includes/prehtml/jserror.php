<?php
if (isset($_POST["message"]) and isset($_POST["file"]) and isset($_POST["line"]) and isset($_POST["url"]))
{
	$query = Constants::$pdo->prepare("INSERT INTO `jserrors` (`date`, `ip`, `message`, `file`, `line`, `url`, `userAgent`, `userId`) VALUES(NOW(), :ip, :message, :file, :line, :url, :userAgent, :userId)");
	$query->execute(array
	(
		":message" => $_POST["message"],
		":ip" => $_SERVER["REMOTE_ADDR"],
		":file" => $_POST["file"],
		":line" => $_POST["line"],
		":url" => $_POST["url"],
		":userAgent" => $_SERVER["HTTP_USER_AGENT"],
		":userId" => Constants::$accountManager->getUserId()
	));
}
else
{
	header("HTTP/1.1 400 Bad Request");
}
exit;
?>