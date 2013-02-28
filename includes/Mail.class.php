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
		$this->replacements = $replacements;
	}
	
	public function send($to, $body)
	{
		if ($this->replacements)
		{
			foreach ($this->replacements as $search => $replace)
			{
				$body = str_replace($search, $replace, $body);
			}
		}
		
		$this->message->setFrom(array(SMTP_FROM_ADDRESS => SMTP_FROM_NAME));
		$this->message->setTo($to);
		$this->message->setBody($body, "text/html");
		
		return $this->mailer->send($this->message);
	}
}
?>