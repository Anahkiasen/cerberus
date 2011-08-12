<?php
function toHTML($string, $reverse = false, $tags = false)
{
	if($tags == true)
	{
		if($reverse == false) return htmlentities($string);
		else return html_entity_decode($string);
	}
	else
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
	'«' => '&laquo;',
	'»' => '&raquo;',
	'€' => '&euro;',
	'’' => '\'');
		
		if($reverse == false) return strtr($string, $table);
		else
		{
			foreach($table as $key => $value) $string = str_replace($value, $key, $string);
			return $string;
		}
	}
}
?>