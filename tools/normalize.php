<?php
/*
	Fonction normalize
	# Supprime tout caractère spécial d'une chaîne : espaces, majuscules, accents, etc.
	
	$string
		Chaîne à normaliser
	$url
		Si la chaîne retournée est destinée à être une URL, on remplace les 
		espaces par des tirets -
		
*/
function normalize($string, $url = false)
{
	$specialChar = array(
	' ' => '_',
	'.' => '',
	'=' => '-',
	'"' => '',
	'\'' => '',
	'/' => '',
	'(' => '',
	')' => '',
	',' => '',
	'?' => '',
	'!' => '',
	'\\' => '',
	'À' => 'A',
	'Á' => 'A',
	'Â' => 'A',
	'Ã' => 'A',
	'Ä' => 'A',
	'Å' => 'A',
	'Æ' => 'A',
	'Ç' => 'C',
	'È' => 'E',
	'É' => 'E',
	'Ê' => 'E',
	'Ë' => 'E',
	'Ì' => 'I',
	'Í' => 'I',
	'Î' => 'I',
	'Ï' => 'I',
	'Ñ' => 'N',
	'Ò' => 'O',
	'Ó' => 'O',
	'Ô' => 'O',
	'Õ' => 'O',
	'Ö' => 'O',
	'Ø' => 'O',
	'Ù' => 'U',
	'Ú' => 'U',
	'Û' => 'U',
	'Ü' => 'U',
	'Ý' => 'Y',
	'Þ' => 'B',
	'ß' => 'Ss',
	'à' => 'a',
	'â' => 'a',
	'ç' => 'c',
	'è' => 'e',
	'é' => 'e',
	'ô' => 'o',
	'ù' => 'u',
	'û' => 'u',
	'Š' => 'S',
	'š' => 's',
	'Ž' => 'Z',
	'ž' => 'z',
	'’' => '');
	
	$return = trim(strtolower(strtr($string, $specialChar)));
	if($url == true) $return = str_replace('_', '-', $return);
	
	return $return;
}
?>