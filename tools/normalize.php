<?php
function normalize ($string) {
	$table = array(
		'Š'=>'S', 'š'=>'s', '?'=>'Dj', '?'=>'dj', 'Ž'=>'Z', 'ž'=>'z', '?'=>'C', '?'=>'c', '?'=>'C', '?'=>'c', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'ô' => 'o',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'é' => 'e', 'è' => 'e', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
		'"' => '', "'" => '', ',' => '', 'â' => 'a', 'é' => 'e', 'è' => 'e',  'û' => 'u', 'ù' => 'u',  'à' => 'a',  'ç' => 'c', '’' => '', '\'' => '', '\\' => '',	'Þ'=>'B', 'ß'=>'Ss', ' ' => '_');
	return strtolower(strtr($string, $table));
}
?>