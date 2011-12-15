<?php
/*
	Fonction toHTML
	# Parse les caractères spéciaux d'une chaine
	
	$string
		Chaîne à parser
	$encode
		TRUE	Encode les caractères en HTML
		FALSE	Décode les caractères en HTML	
	$removeTags
		Encode le texte en HTLML tout en conservant ou non les balises
		intactes (caractères < et > etc)
		
*/
function toHTML($string, $encode = true, $removeTags = false)
{
	$table = array(
	'ç' => '&ccedil;',
	'Ç' => '&Ccedil;',
	'é' => '&eacute;',
	'è' => '&egrave;',
	'ê' => '&ecirc;',
	'ë' => '&euml;',
	'É' => '&Eacute;',
	'È' => '&Egrave;',
	'Ê' => '&Ecirc;',
	'Ë' => '&Euml;',
	'á' => '&aacute;',
	'à' => '&agrave;',
	'â' => '&acirc;',
	'ä' => '&auml;',
	'Á' => '&Aacute;',
	'À' => '&Agrave;',
	'Â' => '&Acirc;',
	'Ä' => '&Auml;',
	'í' => '&iacute;',
	'ì' => '&igrave;',
	'î' => '&icirc;',
	'ï' => '&iuml;',
	'Í' => '&Iacute,',
	'Ì' => '&Igrave;',
	'Î' => '&Icirc;',
	'Ï' => '&Iuml;',
	'ó' => '&oacute;',
	'ò' => '&ograve;',
	'ô' => '&ocirc;',
	'ö' => '&ouml;',
	'Ó' => '&Oacute;',
	'Ò' => '&Ograve;',
	'Ô' => '&Ocirc;',
	'Ö' => '&Ouml;',
	'ú' => '&uacute;',
	'ù' => '&ugrave;',
	'û' => '&ucirc;',
	'ü' => '&uuml;',
	'Ú' => '&Uacute;',
	'Ù' => '&Ugrave;',
	'Û' => '&Ucirc;',
	'Ü' => '&Uuml;',
	'œ' => '&oelig;',
	'"' => '&quot;',
	'«' => '&laquo;',
	'»' => '&raquo;',
	'€' => '&euro;',
	'©' => '&copy;',
	'’' => '\'');
	
	if($removeTags)
	{
		$table['<'] = '&lt;';
		$table['>'] = '&gt;';
	}
	
	if($encode) return strtr($string, $table);
	else
	{
		foreach($table as $key => $value) $string = str_replace($value, $key, $string);
		return $string;
	}
}
?>