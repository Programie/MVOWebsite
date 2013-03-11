<?php
if (isset($_POST["username"]) and isset($_POST["password"]) and Constants::$accountManager->getUserId())
{
	header("Location: " . BASE_URL . "/internalarea");
	exit;
}
if (Constants::$pagePath[1])
{
	switch (Constants::$pagePath[1])
	{
		case "attendancelist":
			if (isset($_POST["attendancelist_dateid"]) and isset($_POST["attendancelist_userid"]))
			{
				$status = $_POST["attendancelist_status"];
				if ($status != "1" and $status != "0")
				{
					$status = null;
				}
				$query = Constants::$pdo->prepare("REPLACE INTO `attendancelist` (`dateId`, `userId`, `changeUserId`, `changeTime`, `status`) VALUES(:dateId, :userId, :changeUserId, NOW(), :status)");
				$query->execute(array
				(
					":dateId" => $_POST["attendancelist_dateid"],
					":userId" => $_POST["attendancelist_userid"],
					":changeUserId" => Constants::$accountManager->getUserId(),
					":status" => $status
				));
				exit;
			}
			break;
		case "forms":
			if (Constants::$pagePath[2])
			{
				$query = Constants::$pdo->prepare("SELECT `name` FROM `forms` WHERE `filename` = :filename");
				$query->execute(array
				(
					":filename" => Constants::$pagePath[2]
				));
				$row = $query->fetch();
				if (Constants::$accountManager->hasPermission("forms." . $row->name))
				{
					$filename = ROOT_PATH . "/files/forms/" . Constants::$pagePath[2];
					if (file_exists($filename))
					{
						$file = fopen($filename, "r");
						{
							header("Content-Description: Formular Herunterladen");
							header("Content-Type: application/octet-stream");
							header("Content-Disposition: attachment; filename=" . Constants::$pagePath[2]);
							header("Content-Transfer-Encoding: chunked");
							header("Expires: 0");
							header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
							header("Pragma: public");
							while ($chunk = fread($file, 4096))
							{
								echo $chunk;
							}
							fclose($file);
							exit;
						}
					}
				}
			}
			break;
		case "logout":
			Constants::$accountManager->logout();
			header("Location: " . BASE_URL . "/internalarea");
			exit;
	}
}
else
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
?>