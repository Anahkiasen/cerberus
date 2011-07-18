<?php
function send_mail($mail, $sujet, $contenu, $expediteur = "4G Technology")
{
	global $index;
	$boundary = '-----=' .md5(rand());
	$boundary_alt = '-----=' .md5(rand());
	
	$message_html = '<html><head><link href="' .$index['http']. 'css/styles.css" rel="stylesheet" type="text/css" /></head>
	<body style="background: none"><div id="mail">
	' .$contenu. '
	</div></body></html>';
	$message_html = preg_replace('#<img src="(.+)" />#isU', '<img src="' .$index['http']. '$1">', $message_html);
	$message_html = str_replace($index['http']. 'http:', 'http:', $message_html);
	$message_text = strip($contenu);
	
	// Header
	$header = "From: \"" .$expediteur. "\"<" .$index['mail']. ">\r\n";
	$header.= "Bcc: " .$mail. "\r\n";
	$header.= "MIME-Version: 1.0\r\n";
	$header.= "Content-Type: multipart/mixed;\r\n boundary=\"$boundary\"\r\n";
	$message = "\r\n--".$boundary. "\r\n";
	$message.= "Content-Type: multipart/alternative;\r\n boundary=\"$boundary_alt\"\r\n";
	$message.= "\r\n--".$boundary_alt. "\r\n";
		
	// Message Texte
	$message.= "Content-Type: text/plain; charset=\"UTF-8\"\r\n";
	$message.= "Content-Transfer-Encoding: 8bit\r\n";
	$message.= "\r\n".$message_text. "\r\n";
	$message.= "\r\n--".$boundary_alt. "\r\n";
	
	// Message HTML
	$message.= "Content-Type: text/html; charset=\"UTF-8\"\r\n";
	$message.= "Content-Transfer-Encoding: 8bit\r\n";
	$message.= "\r\n".$message_html. "\r\n";
	$message.= "\r\n--".$boundary_alt."--\r\n";
	
	/*
	if($attachement != '')
	{
		$message .= "\r\n--".$boundary. "\r\n";
		$data = chunk_split(base64_encode(file_get_contents($attachement)));
		
		$realFilename = explode('/', $attachement);
		$realFilename = $realFilename[count($realFilename)-1];
		
		$message .= "Content-Type: text/csv; name=" .$realFilename. "
		Content-Transfer-Encoding: base64
		Content-Disposition: attachment\r\n\r\n";
		$message .= $data;
		$message .= "\r\n--".$boundary. "\r\n";
	}*/
	
	$melto = str_replace('<', '', $mail);
	$melto = str_replace('>', '', $melto);
	if(mail('', $sujet, $message, $header)) echo '<p class="navbargreen">L\'email a bien été envoyé à ' .$melto. '.</p>';
}
?>