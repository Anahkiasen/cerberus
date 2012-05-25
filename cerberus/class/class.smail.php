<?php
class smail
{
	// Core
	private $destinataire;
	private $sujet;
	private $contenu;
	private $messageText;

	// Options
	private $absoluteURL;
	private $messageHTML;
	private $attachement;
	private $expediteurAlias;
	private $expediteurMail;

	// Barrières
	private $boundary;
	private $boundary_alt;

	// Constructeur
	function __construct($destinataire, $sujet, $contenu)
	{
		$this->destinataire = $destinataire;
		$this->sujet = $sujet;

		// Formulaire ou texte
		if(is_array($contenu))
		{
			foreach($contenu as $key => $value)
			{
				if(is_array($value))
				{
					$this->contenu .= $key. ' : <br />';
					foreach($value as $v2) $this->contenu .= '- ' .$v2. '<br />';
					$this->contenu .= '<br /><br />';
				}
				else $this->contenu .= $key. ' : ' .$value. '<br /><br />';
			}
		}
		else $this->contenu = $contenu;
		$this->messageText = str::unhtml($this->contenu);

		if(!is_array($destinataire) and strpos($destinataire, ',') !== FALSE)
			$this->destinaire = explode(', ', $destinataire);

		$this->boundary = '-----=' .md5(rand());
		$this->boundary_alt = '-----=' .md5(rand());
	}

	// Message en HTML
	function messageHTML($absoluteURL = NULL)
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
				<h1>' .$this->sujet. '</h1>
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
	function send($header = NULL)
	{
		if(!empty($this->expediteurMail)) $header .= "From: \"" .$this->expediteurAlias. "\"<" .$this->expediteurMail. ">\r\n";
		if(is_array($this->destinataire))
		{
			foreach($this->destinataire as $key => $value) $destinataires[$key] = '<' .$value. '>';
			$header .= "Bcc: " .implode(',', $destinataires). "\r\n";
			$this->destinataire = NULL;
		}
		$header .= "MIME-Version: 1.0\n";
		$header .= "Content-Type: multipart/alternative; boundary=\"".$this->boundary_alt."\"";

		$message = "--".$this->boundary_alt."\n";
		$message .= "Content-Type: text/plain\n";
		$message .= "charset=\"iso-8859-1\"\n";
		$message .= "Content-Transfer-Encoding: 8bit\n\n";
		$message .= $this->messageText;

		// Message HTML
		if($this->messageHTML)
		{
			$message .= "\n\n--".$this->boundary_alt."\n";
			$message .= "Content-Type: text/html; ";
			$message .= "charset=\"utf-8\"; ";
			$message .= "Content-Transfer-Encoding: 8bit;\n\n";
			$message .= $this->messageHTML;
		}

		// Pièce jointe
		/*if($this->attachement and TRUE == FALSE)
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
		$message .= "\n--".$this->boundary_alt."--";

		if(mail($this->destinataire, $this->sujet, $message, $header)) return true;
		else return false;
	}

	function __toString()
	{
		str::status($this->send(), l::get('mail.sent'), l::get('mail.error'));
	}
}
?>
