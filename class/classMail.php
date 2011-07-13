<?php
class sendMail
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
	
	private $endReturn = TRUE;
	
	// Barrières
	private $boundary;
	private $boundary_alt;
	
	// Constructeur
	function __construct($destinataire, $sujet, $contenu, $endReturn = TRUE)
	{
		$this->destinataire = $destinataire;
		$this->sujet = $sujet;
		$this->endReturn = $endReturn;
		
		// Formulaire ou texte
		if(is_array($contenu))
		{
			postVar($contenu);
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
		$this->messageText = stripHTML($this->contenu);
		
		if(findString(',', $destinataire)) $this->destinaire = explode(', ', $destinataire);
		
		$this->boundary = '-----=' .md5(rand());
		$this->boundary_alt = '-----=' .md5(rand());
	}
	
	// Message en HTML
	function messageHTML($absoluteURL)
	{
		$this->absoluteURL = $absoluteURL;
		
		$this->messageHTML = '
		<html>
		<head>
			<link href="' .$this->absoluteURL. 'css/styles.css" rel="stylesheet" type="text/css" />
		</head>
		
		<body>
			<div id="mail-body">
				' .$this->contenu. '
			</div>
		</body>
		</html>';
		
		$this->messageHTML = preg_replace('#<img src="(.+)" />#isU', '<img src="' .$absoluteURL. '$1">', $this->messageHTML);
		$this->messageHTML = str_replace($index['http']. 'http:', 'http:', $this->messageHTML);
	}
	
	// Précision de l'expéditeur
	function setExpediteur($alias, $email)
	{
		$this->expediteurAlias = $alias;
		$this->expediteurMail = $email;
	}
		
	// Envoi du mail
	function send($header = '')
	{
		if(!empty($this->expediteurMail)) $header .= "From: \"" .$this->expediteurAlias. "\"<" .$this->expediteurMail. ">\r\n";
		if(is_array($this->destinataire))
		{
			foreach($this->destinataire as $key => $value) $destinataires[$key] = '<' .$value. '>'; 
			$header .= "Bcc: " .implode(',', $destinataires). "\r\n";
			$this->destinataire = '';
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
			$message .= "charset=\"iso-8859-1\"; ";
			$message .= "Content-Transfer-Encoding: 8bit;\n\n";
			$message .= $this->messageHTML;
		}
		
		// Pièce jointe
		if($this->attachement and TRUE == FALSE)
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
		$message .= "\n--".$this->boundary_alt."--";
		
		if(mail($this->destinataire, $this->sujet, $message, $header))
		{
			if($this->endReturn == false) echo display('Votre message a bien été envoyé');
			return true;
		}
		else return false;
	}
}
?>