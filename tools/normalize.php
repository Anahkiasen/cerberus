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
	'«' => '',
	'»' => '',
	'\'' => '',
	'/' => '',
	'(' => '',
	')' => '',
	',' => '',
	'’' => '',
	':' => '',
	';' => '',
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
	'à' => 'a',
	'â' => 'a',
	'á' => 'a',
	'ä' => 'a',
	
	'Ç' => 'C',
	'ç' => 'c',
	
	'È' => 'E',
	'É' => 'E',
	'Ê' => 'E',
	'Ë' => 'E',
	'è' => 'e',
	'é' => 'e',
	'ê' => 'e',
	'ë' => 'e',
	
	'Ì' => 'I',
	'Í' => 'I',
	'Î' => 'I',
	'Ï' => 'I',
	'ì' => 'i',
	'í' => 'i',
	'î' => 'i',
	'ï' => 'i',
	
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
	'ù' => 'u',
	'ú' => 'u',
	'û' => 'u',
	'ü' => 'u',
	
	'Ý' => 'Y',
	'Þ' => 'B',
	'ß' => 'Ss',
	'ô' => 'o',
	'Š' => 'S',
	'š' => 's',
	'Ž' => 'Z',
	'ž' => 'z');
	
	$return = trim(strtolower(strtr($string, $specialChar)));
	if($url == true) $return = str_replace('_', '-', $return);
	$return = str_replace('---', '-', $string);
	
	return $return;
}
?>