<?php
/**
 *
 * Mail
 * Creates and send an email
 *
 * @package Cerberus
 */
class Mail
{
	// Mail core --------------------------------------------------- /

	private $to          = null;
	private $subject     = null;
	private $content     = null;
	private $messageRaw  = null;

	// Mail options ------------------------------------------------ /

	private $domain      = null;
	private $messageHTML = null;
	private $attachement = null;
	private $senderAlias = null;
	private $senderMail  = null;
	private $useBcc      = false;

	// Boundaries -------------------------------------------------- /

	private $boundary    = null;
	private $boundaryAlt = null;

	//////////////////////////////////////////////////////////////////
	/////////////////////////// CREATE A MAIL ////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Creates an email to send
	 *
	 * @param string $to      To whom ?
	 * @param string $subject The mail's subject
	 * @param string $content Its content
	 */
	public function __construct($to, $subject, $content)
	{
		// Create boundaries
		$this->boundary    = '-----=' .md5(rand());
		$this->boundaryAlt = '-----=' .md5(rand());

		// Map variables
		$this->to      = $to;
		$this->subject = $subject;
		$this->content = $content;

		// If the content to pass is an array, flatten it
		if(is_array($this->content)) $this->content = self::flatten($this->content);

		// Create raw version of the message
		$this->messageRaw = str::unhtml($this->content);

		// If we have several recipients
		if(!is_array($to) and str::find(',', $to))
			$this->destinaire = explode(', ', $to);

		// Set domain to configured one
		$this->setDomain(config::get('http'));
	}

	/**
	 * Generates an HTML version for the current email
	 */
	public function createHTML()
	{
		$this->messageHTML = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<link href="' .$this->domain.PATH_COMMON. 'css/mail.css" rel="stylesheet" type="text/css" />
		</head>

		<body id="mail">
			<div align="center" width="600">
				<div id="header"></div>
				<div id="corps">
					<h1>' .$this->subject. '</h1>
					<div id="message">
						' .$this->content. '
					</div>
				</div>
			</div>
		</body>
		</html>';

		// Fix the images paths
		$this->messageHTML = preg_replace(
			'#<img (.+)?src="(.+)"( /)?>#isU',
			'<img $1src="' .$this->domain. '$2"$3>',
			$this->messageHTML);

		// Fix possible double domains
		$this->messageHTML = str_replace($this->domain. 'http', 'http', $this->messageHTML);
	}

	/**
	 * Set a sender for the current email
	 *
	 * @param string $email The email of the sender
	 * @param string $alias The name displayed as the send
	 */
	public function setSender($email, $alias = null)
	{
		$this->senderAlias = $alias ? $alias : $email;
		$this->senderMail  = $email;
	}

	/**
	 * Set a domain for the current email (for HTML)
	 *
	 * @param string $domain A domain to use in the HTML version
	 */
	public function setDomain($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * Whether to use BCC or not
	 *
	 * @param boolean $bcc Use BCC or not
	 */
	public function setBcc($bcc = false)
	{
		$this->useBcc = $bcc;
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////////// SEND A MAIL //////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Send the email and return its state
	 *
	 * @param  string $header The base header of the email
	 * @return [type]         [description]
	 */
	public function send($header = null)
	{
		// If we have a sender, add it to headers
		if(!empty($this->senderMail))
			$header .= "From: \"" .$this->senderAlias. "\"<" .$this->senderMail. ">\r\n";

		// If we have several recipients
		if(is_array($this->to))
		{
			// Put email into < >
			foreach($this->to as $key => $email)
				$tos[$key] = '<' .$email. '>';

			// Put them all into hidden copy if we want
			if($this->useBcc)
			{
				$header .= "Bcc: " .implode(',', $tos). "\r\n";
				$this->to = null;
			}
		}

		// Set MIME-type
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/alternative; boundary=\"".$this->boundaryAlt."\"";

		// Set raw message
		$message = "--".$this->boundaryAlt."\n";
		$message .= "Content-Type: text/plain\n";
		$message .= "charset=\"utf-8\"\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= $this->messageRaw;

		// Set HTML message
		if($this->messageHTML)
		{
			$message .= "\n\n--".$this->boundaryAlt."\n";
			$message .= "Content-Type: text/html; ";
			$message .= "charset=\"utf-8\"; ";
			$message .= "Content-Transfer-Encoding: 8bit;\n\n";
			$message .= $this->messageHTML;
		}

		// TODO : Add attached files
		/*
		if($this->attachement and true == false)
		{
			$message .= "\r\n--".$this->boundary. "\r\n";
			$data = chunk_split(base64_encode(file_get_contents($attachement)));

			$realFilename = explode('/', $attachement);
			$realFilename = $realFilename[count($realFilename)-1];

			$message .= "Content-Type: text/csv; name=" .$realFilename. "
			Content-Transfer-Encoding: base64
			Content-Disposition: attachment\r\n\r\n";
			$message .= $data;
			$message .= "\r\n--".$this->boundary. "\r\n";
		}
		*/

		// Close message
		$message .= "\n--".$this->boundaryAlt."--";

		// Send it and return how it went
		try
		{
			$status = mail($this->to, $this->subject, $message, $header);
			if(!$status) throw new Exception(l::get('mail.error'));
		}
		catch(Exception $e)
		{
			$additional = get_object_vars($this);
			Debug::handle($e, null, null, $additional);
		}

		return $status;
	}

	/**
	 * Sends the mail and return the corresponding status
	 *
	 * @return string An alert stating
	 */
	public function __toString()
	{
		str::status($this->send(), l::get('mail.sent'), l::get('mail.error'));
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////////// PUBLIC TOOLKIT ///////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Flatten an array to be displayed in an email
	 *
	 * @param  array  $array An array to flatten
	 * @return string        A flattened array
	 */
	public static function flatten($array)
	{
		if(!is_array($array)) return $array;

		$return = null;
		foreach($array as $key => $value)
		{
			if(is_bool($value)) $value = str::boolprint($value);

			if(is_array($value))
			{
				$return .= '<p><strong>' .forms::getReadable($key). '</strong> : <br />';
				foreach($value as $k2 => $v2)
				{
					if(is_bool($v2)) $v2 = str::boolprint($v2);
					$this->content .= is_numeric($k2)
						? '- ' .$k2. ' : ' .$v2. '<br />'
						: '- ' .$v2. '<br />';
				}
				$return .= '</p>';
			}
			else $return .= '<p><strong>' .forms::getReadable($key). '</strong> : ' .$value. '</p>';
		}
		return $return;
	}
}
