<?php
/**
 *
 * Mail
 * Creates and send an email
 *
 * @package Cerberus
 */
class Smail
{
	// Mail core --------------------------------------------------- /

	private $to          = null;
	private $subject     = null;
	private $content     = null;
	private $messageRaw = null;

	// Mail options ------------------------------------------------ /

	private $absoluteURL = null;
	private $messageHTML = null;
	private $attachement = null;
	private $fromAlias   = null;
	private $fromMail    = null;

	// Boundaries -------------------------------------------------- /

	private $boundary    = null;
	private $boundaryAlt = null;

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
		$this->subjet  = $subject;
		$this->content = $content;

		// If the content to pass is an array, flatten it
		if(is_array($this->content))
		{
			foreach($this->content as $key => $value)
			{
				if(is_array($value))
				{
					$this->contenu .= '<p>'.$key. ' : <br />';
					foreach($value as $k2 => $v2)
						$this->contenu .= is_numeric($k2)
							? '- ' .$k2. ' : ' .$v2. '<br />'
							: '- ' .$v2. '<br />';
					$this->contenu .= '</p>';
				}
				else $this->contenu .= '<p>' .$key. ' : ' .$value. '</p>';
			}
		}

		// Create raw version of the message
		$this->messageRaw = str::unhtml($this->contenu);

		if(!is_array($to) and strpos($to, ',') !== false)
			$this->destinaire = explode(', ', $to);


	}

	// Message en HTML
	function messageHTML($absoluteURL = null)
	{
		$this->absoluteURL = config::get('http', $absoluteURL);

		$this->messageHTML = '
		<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<link href="' .$this->absoluteURL.PATH_COMMON. 'css/mail.css" rel="stylesheet" type="text/css" />
		</head>

		<body id="mail">
			<div id="header"></div>
			<div id="corps">
				<h1>' .$this->subject. '</h1>
				<div id="message">
					' .$this->contenu. '
				</div>
			</div>
		</body>
		</html>';

		$this->messageHTML = preg_replace('#<img src="(.+)" />#isU', '<img src="' .$this->absoluteURL. '$1">', $this->messageHTML);
		$this->messageHTML = str_replace($this->absoluteURL. 'http:', 'http:', $this->messageHTML);
	}

	// Précision de l'expéditeur
	function setExpediteur($alias, $email)
	{
		$this->expediteurAlias = $alias;
		$this->expediteurMail = $email;
	}

	// Envoi du mail
	function send($header = null)
	{
		if(!empty($this->expediteurMail)) $header .= "From: \"" .$this->expediteurAlias. "\"<" .$this->expediteurMail. ">\r\n";
		if(is_array($this->to))
		{
			foreach($this->to as $key => $value) $tos[$key] = '<' .$value. '>';
			$header .= "Bcc: " .implode(',', $tos). "\r\n";
			$this->to = null;
		}
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/alternative; boundary=\"".$this->boundaryAlt."\"";

		$message = "--".$this->boundaryAlt."\n";
		$message .= "Content-Type: text/plain\n";
		$message .= "charset=\"iso-8859-1\"\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= $this->messageRaw;

		// Message HTML
		if($this->messageHTML)
		{
			$message .= "\n\n--".$this->boundaryAlt."\n";
			$message .= "Content-Type: text/html; ";
			$message .= "charset=\"utf-8\"; ";
			$message .= "Content-Transfer-Encoding: 8bit;\n\n";
			$message .= $this->messageHTML;
		}

		// Pièce jointe
		/*if($this->attachement and true == false)
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
		}*/
		$message .= "\n--".$this->boundaryAlt."--";

		if(mail($this->to, $this->subject, $message, $header)) return true;
		else return false;
	}

	function __toString()
	{
		str::status($this->send(), l::get('mail.sent'), l::get('mail.error'));
	}
}
