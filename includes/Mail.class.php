<?php
require_once "swift/swift_required.php";

class Mail
{
	private $mailer;
	private $message;
	private $replacements;
	
	public function __construct($subject = null, $replacements = null)
	{
		$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT, SMTP_ENCRYPTION);
		$transport->setUsername(SMTP_USERNAME);
		$transport->setPassword(SMTP_PASSWORD);
		
		$this->mailer = Swift_Mailer::newInstance($transport);
		
		if ($subject)
		{
			$this->newMessage($subject);
		}
		
		if ($replacements)
		{
			$this->setReplacements($replacements);
		}
	}
	
	public function newMessage($subject)
	{
		$this->message = Swift_Message::newInstance($subject);
	}
	
	public function setReplacements($replacements)
	{
		// Default replacements
		$this->replacements = array
		(
			"%BASE_URL%" => BASE_URL,
			"%WEBMASTER_EMAIL%" => WEBMASTER_EMAIL
		);
		
		$this->replacements = array_merge($this->replacements, $replacements);
	}
	
	public function send($templateName, $to, $cc = null, $bcc = null)
	{
		$body = @file_get_contents(ROOT_PATH . "/includes/mails/" . $templateName . ".html");
		
		if (!$body)
		{
			return false;
		}
		
		if ($this->replacements)
		{
			foreach ($this->replacements as $search => $replace)
			{
				$body = str_replace($search, $replace, $body);
			}
		}
		
		$this->message->setFrom(array(SMTP_FROM_ADDRESS => SMTP_FROM_NAME));
		$this->message->setTo($to);
		if ($cc)
		{
			$this->message->setCc($cc);
		}
		if ($bcc)
		{
			$this->message->setBcc($bcc);
		}
		$this->message->setBody($body, "text/html");
		
		return $this->mailer->send($this->message);
	}
}
?>