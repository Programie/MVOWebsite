<h1>Email-Adresse best&auml;tigen</h1>

<?php
if (Constants::$pagePath[2])
{
	$query = Constants::$pdo->prepare("SELECT `id`, `email`AS `oldEmail`, `newEmail` FROM `users` WHERE `id` = :id AND `newEmailChangeDate` IS NOT NULL AND `newEmailChangeDate` = :date");
	$query->execute(array(":id" => Constants::$accountManager->getUserId(), ":date" => date("Y-m-d H:i:s", Constants::$pagePath[2])));
	if ($query->rowCount())
	{
		$row = $query->fetch();
		if (Constants::$pagePath[2] >= time() - TIMEOUT_CONFIRMLINK)
		{
			$query = Constants::$pdo->prepare("UPDATE `users` SET `email` = :email, `newEmail` = NULL, `newEmailChangeDate` = NULL WHERE `id` = :id");
			$query->execute(array(":email" => $row->newEmail, ":id" => Constants::$accountManager->getUserId()));

			$userData = Constants::$accountManager->getUserData();

			$replacements = array("FIRSTNAME" => $userData->firstName, "NEWEMAILADDRESS" => $userData->email);
			$mail = new Mail("Email-Adresse geändert", $replacements);
			$mail->setTemplate("email-changed");
			$mail->setTo($row->oldEmail);
			$mail->send();

			echo "<div class='alert-success'>Die Email-Adresse wurde erfolgreich auf <b>" . $row->newEmail . "</b> ge&auml;ndert.</div>";
		}
		else
		{
			echo "<div class='alert-error'>Die G&uuml;ltigkeit des Schl&uuml;ssels ist abgelaufen! Bitte versuche es erneut oder wende dich an den Webmaster.</div>";
		}
	}
	else
	{
		echo "<div class='alert-error'>Ung&uuml;ltiger Schl&uuml;ssel!</div>";
	}
}
?>