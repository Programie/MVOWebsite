<?php
class Mail
{
	/**
	 * @var Swift_Mailer
	 */
	private $mailer;
	/**
	 * @var Swift_Message
	 */
	private $message;
	/**
	 * @var StdClass
	 */
	private $recipients;
	/**
	 * @var array
	 */
	private $replacements;
	/**
	 * @var array|string
	 */
	private $replyTo;
	/**
	 * @var string
	 */
	private $templateName;

	public function __construct($subject = null, $replacements = null)
	{
		$transport = Swift_SmtpTransport::newInstance(SMTP_SERVER, SMTP_PORT, SMTP_ENCRYPTION);
		$transport->setUsername(SMTP_USERNAME);
		$transport->setPassword(SMTP_PASSWORD);

		$this->mailer = Swift_Mailer::newInstance($transport);

		$this->clearMessageData();

		if ($subject)
		{
			$this->newMessage($subject);
		}

		if ($replacements)
		{
			$this->setReplacements($replacements);
		}
		else
		{
			$this->setReplacements(array());
		}
	}

	// Message preparation

	private function clearMessageData()
	{
		$this->recipients = new StdClass;
		$this->replyTo = null;
	}

	public function newMessage($subject)
	{
		$this->message = Swift_Message::newInstance($subject);
		$this->clearMessageData();
	}

	// Replacements

	public function addReplacement($key, $value)
	{
		$this->replacements[$key] = $value;
	}

	public function setReplacements($replacements)
	{
		$this->replacements = $replacements;
	}

	// Template

	public function setTemplate($templateName)
	{
		$this->templateName = $templateName;
	}

	// Reply To

	public function setReplyTo($address)
	{
		$this->replyTo = $address;
	}

	// Recipients

	public function setTo($recipients)
	{
		$this->recipients->to = $recipients;
	}

	public function setCc($recipients)
	{
		$this->recipients->cc = $recipients;
	}

	public function setBcc($recipients)
	{
		$this->recipients->bcc = $recipients;
	}

	// Send

	public function send()
	{
		// Default replacements
		$this->addReplacement("baseUrl", BASE_URL);
		$this->addReplacement("webmasterEmail", WEBMASTER_EMAIL);

		$body = @file_get_contents(ROOT_PATH . "/includes/mails/" . $this->templateName . ".html");

		if (!$body)
		{
			return false;
		}

		$mustache = new Mustache_Engine();

		$body = $mustache->render($body, $this->replacements);

		$this->message->setBody($body, "text/html");

		$this->message->setFrom(array(SMTP_FROM_ADDRESS => SMTP_FROM_NAME));
		$this->message->setTo($this->recipients->to);

		if ($this->recipients->cc)
		{
			$this->message->setCc($this->recipients->cc);
		}

		if ($this->recipients->bcc)
		{
			$this->message->setBcc($this->recipients->bcc);
		}

		if ($this->replyTo)
		{
			$this->message->setReplyTo($this->replyTo);
		}

		return $this->mailer->send($this->message);
	}
}

?>