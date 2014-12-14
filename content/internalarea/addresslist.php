<?php
require_once __DIR__ . "/../../includes/FileUploader.class.php";

if ($_SERVER["REQUEST_METHOD"] == "POST")
{
	$error = "Unknown";

	$targetUsers = array();

	if (isset($_POST["text"]) and $_POST["text"])
	{
		$userQuery = Constants::$pdo->prepare("SELECT `email`, `firstName`, `lastName` FROM `users` WHERE `id` = :id");

		foreach (explode(",", $_POST["recipients"]) as $userId)
		{
			$userQuery->execute(array
			(
				":id" => $userId
			));

			if (!$userQuery->rowCount())
			{
				continue;
			}

			$userRow = $userQuery->fetch();

			$targetUsers[] = (int) $userId;

			if ($userRow->email)
			{
				$mailRecipients[$userRow->email] = $userRow->firstName . " " . $userRow->lastName;
			}
		}

		try
		{
			$fileUploader = new FileUploader();
			$attachedFiles = $fileUploader->getFiles();

			$query = Constants::$pdo->prepare("
				INSERT INTO `messages`
				SET
					`date` = NOW(),
					`userId` = :userId,
					`text` = :text
			");

			$query->execute(array
			(
				":userId" => Constants::$accountManager->getUserId(),
				":text" => $_POST["text"]
			));

			$messageId = Constants::$pdo->lastInsertId();

			$query = Constants::$pdo->prepare("
				INSERT INTO `messagetargets`
				SET
					`messageId` = :messageId,
					`userId` = :userId
			");

			foreach ($targetUsers as $userId)
			{
				$query->execute(array
				(
					":messageId" => $messageId,
					":userId" => $userId
				));
			}

			$query = Constants::$pdo->prepare("
				INSERT INTO `messagefiles`
				SET
					`messageId` = :messageId,
					`fileId` = :fileId
			");

			foreach ($attachedFiles as $file)
			{
				$query->execute(array
				(
					":messageId" => $messageId,
					":fileId" => $file->id
				));
			}

			$userData = Constants::$accountManager->getUserData();

			$userAddress = array($userData->email => $userData->firstName . " " . $userData->lastName);

			$mail = new Mail("Neue Nachricht im Internen Bereich", array
			(
				"firstName" => $userData->firstName,
				"lastName" => $userData->lastName,
				"content" => formatText($_POST["text"]),
				"attachments" => $attachedFiles
			));
			$mail->setTemplate("message");
			$mail->setTo($mailRecipients);

			if ($_POST["sendCopy"])
			{
				$mail->setCc($userAddress);
			}

			$mail->setReplyTo($userAddress);

			if (!$mail->send())
			{
				throw new Exception("Unable to send mail");
			}

			header("Location: " . BASE_URL . "/internalarea/messages/" . $messageId . "?sendinfo");
			exit;
		}
		catch (Exception $exception)
		{
			$error = $exception->getMessage() . " (Code " . $exception->getCode() . ")";
		}
	}

	echo "
		<script type='text/javascript'>
			$(function()
			{
				$('#addresslist_writemessage_error_message').text(" . json_encode($error) . ");
				$('#addresslist_writemessage_error').show();

				setAddressListSelection(" . json_encode($targetUsers) . ");
				$('#addresslist_sendmessage_text').val(" . json_encode($_POST["text"]) . ");
			});
		</script>
	";
}
?>
<script type="text/html" id="addresslist_groupbox_template">
	{{#.}}
		<input type="checkbox" id="addresslist_groupcheckbox_{{name}}" data-name="{{name}}" checked/><label for="addresslist_groupcheckbox_{{name}}">{{title}}</label>
	{{/.}}
</script>

<script type="text/html" id="addresslist_tbody_template">
	{{#.}}
		<tr class="addresslist-row {{#groups}}addresslist-group-{{.}} {{/groups}}" data-userid="{{id}}">
			<td class="no-print">{{#email}}<input type="checkbox"/>{{/email}}</td>
			<td>{{firstName}}</td>
			<td>{{lastName}}</td>
			<td><a href="mailto:{{email}}">{{email}}</a></td>
			<td>
				{{#phoneNumbers}}
					<div>{{category}} ({{subCategory}}): {{number}}</div>
				{{/phoneNumbers}}
			</td>
		</tr>
	{{/.}}
</script>

<h1>Adressenliste</h1>

<div class="alert-error" id="addresslist_writemessage_error">
	Die Nachricht konnte nicht gesendet werden! Bitte versuche es erneut oder wende dich an den <a href="mailto:<?php echo WEBMASTER_EMAIL;?>">Webmaster</a>.
	<p>Fehler: <span id="addresslist_writemessage_error_message"></span></p>
</div>

<fieldset>
	<legend><input type="checkbox" id="addresslist_groupbox_checkall" checked/><label for="addresslist_groupbox_checkall">Gruppen</label></legend>
	<div id="addresslist_groupbox"></div>
</fieldset>

<table id="addresslist_table" class="table {sortlist: [[2,0],[1,0]]}">
	<thead>
		<tr>
			<th class="no-print" data-sorter="false"><input type="checkbox" id="addresslist_table_checkall"/></th>
			<th>Vorname</th>
			<th>Nachname</th>
			<th>Email</th>
			<th>Telefon</th>
		</tr>
	</thead>
	<tbody id="addresslist_tbody"></tbody>
</table>

<fieldset id="addresslist_sendmessage" class="no-print">
	<legend>Nachricht senden</legend>

	<form action="/internalarea/addresslist" method="post" enctype="multipart/form-data">
		<textarea id="addresslist_sendmessage_text" name="text" rows="15" cols="15"></textarea>

		<fieldset id="addresslist_sendmessage_attachments">
			<legend>Anh&auml;nge</legend>
		</fieldset>

		<input type="hidden" id="addresslist_sendmessage_sendcopy" name="sendCopy"/>
		<input type="hidden" id="addresslist_sendmessage_recipients" name="recipients"/>
		<input type="hidden" name="sendToken" value="<?php echo TokenManager::getSendToken("addresslist_sendmessage", true); ?>"/>
		<input type="submit" value="Senden"/>
	</form>
</fieldset>

<div id="addresslist_sendmessage_confirm" title="Nachricht senden">
	<p id="addresslist_sendmessage_confirm_text1"></p>
	<ul id="addresslist_sendmessage_confirm_recipients"></ul>

	<p id="addresslist_sendmessage_confirm_text2"><b>Anh&auml;nge:</b></p>
	<ul id="addresslist_sendmessage_confirm_attachments"></ul>

	<input id="addresslist_sendmessage_confirm_sendcopy" type="checkbox"/><label for="addresslist_sendmessage_confirm_sendcopy">Eine Kopie an mich senden</label>
</div>