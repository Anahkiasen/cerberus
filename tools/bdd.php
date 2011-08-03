<?php
/*
	Fonction bdd
	# Encode une chaîne pour insértion dans une base de données
	
	Fonction html
	# Décode une chaîne encodée pour affichage au sein d'une page
	
	$string
		La chaîne à encoder/décoder
*/
function bdd($string)
{
	if(ctype_digit($string)) $string = intval($string);
	else
	{
		$string = mysql_real_escape_string($string);
		$string = addcslashes($string, '%_');
	}
	return $string;
}
function bddArray($array)
{
	if(is_array($array))
	{
		$plainedArray = array();
		foreach($array as $key => $value) $plainedArray[] = $key. '="' .bdd($value). '"';
		return implode(',', $plainedArray);
	}
}
function html($string)
{
	$string = htmlspecialchars($string);
	return $string = stripslashes($string);
}
?>