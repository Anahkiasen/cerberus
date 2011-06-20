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
?>