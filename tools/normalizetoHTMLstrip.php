<?php
function normalize ($string) {
	$table = array(
		'Š'=>'S', 'š'=>'s', '?'=>'Dj', '?'=>'dj', 'Ž'=>'Z', 'ž'=>'z', '?'=>'C', '?'=>'c', '?'=>'C', '?'=>'c', 'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'ô' => 'o',
		'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E', 'é' => 'e', 'è' => 'e', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
		'"' => '', "'" => '', ',' => '', 'â' => 'a', 'é' => 'e', 'è' => 'e',  'û' => 'u', 'ù' => 'u',  'à' => 'a',  'ç' => 'c', '’' => '', '\'' => '', '\\' => '',	'Þ'=>'B', 'ß'=>'Ss', ' ' => '_');
	return strtolower(strtr($string, $table));
}
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
		'é' => 'é',
		'è' => 'è',
		'ê' => 'ê',
		'ë' => 'ë',
		'É' => 'É',
		'È' => 'È',
		'Ê' => 'Ê',
		'Ë' => 'Ë',
		'á' => 'á',
		'à' => 'à',
		'â' => 'â',
		'ä' => 'ä',
		'Á' => 'Á',
		'À' => 'À',
		'Â' => 'Â',
		'Ä' => 'Ä',
		'í' => 'í',
		'ì' => 'ì',
		'î' => 'î',
		'ï' => 'ï',
		'Í' => 'Í',
		'Ì' => 'Ì',
		'Î' => 'Î',
		'Ï' => 'Ï',
		'ó' => 'ó',
		'ò' => 'ò',
		'ô' => 'ô',
		'ö' => 'ö',
		'Ó' => 'Ó',
		'Ò' => 'Ò',
		'Ô' => 'Ô',
		'Ö' => 'Ö',
		'ú' => 'ú',
		'ù' => 'ù',
		'û' => 'û',
		'ü' => 'ü',
		'Ú' => 'Ú',
		'Ù' => 'Ù',
		'Û' => 'Û',
		'Ü' => 'Ü',
		'œ' => 'œ',
		'«' => '«',
		'»' => '»',
		'€' => '€',
		'’' => '\'');
		
		if($reverse == false) return strtr($string, $table);
		else
		{
			foreach($table as $key => $value) $string = str_replace($value, $key, $string);
			return $string;
		}
	}
}
function strip($chain)
{
	$contenu = preg_replace('#<p>(.+)</p>#isU', '$1',$chain);
	$contenu = preg_replace('#<em>(.+)</em>#isU', '$1', $contenu);
	$contenu = preg_replace('#<p class="navbar">(.+)</p>#isU', '$1', $contenu);
	$contenu = preg_replace('#<img src="(.+)" />#isU', '', $contenu);
	$contenu = preg_replace('#<span class="(.+)">(.+)</span>#isU', '$2', $contenu);
	return $contenu;
}
?>