<?php
if (isset($_POST["attendancelist_dateid"]) and isset($_POST["attendancelist_userid"]))
{
	$status = $_POST["attendancelist_status"];
	$dateId = intval($_POST["attendancelist_dateid"]);
	$userId = intval($_POST["attendancelist_userid"]);
	if ($status != "1" and $status != "0")
	{
		$status = null;
	}
	if ($dateId and $userId)
	{
		$query = Constants::$pdo->prepare("
			INSERT INTO `attendancelist` (`dateId`, `userId`, `changeUserId`, `changeTime`, `status`)
			VALUES(:dateId, :userId, :changeUserId, NOW(), :status)
			ON DUPLICATE KEY UPDATE
			`changeUserId` = :changeUserId, `changeTime` = NOW(), `status` = :status
		");
		$query->execute(array(":dateId" => $_POST["attendancelist_dateid"], ":userId" => $_POST["attendancelist_userid"], ":changeUserId" => Constants::$accountManager->getUserId(), ":status" => $status));
		if ($query->rowCount())
		{
			echo "ok";
		}
	}
	exit;
}
?>