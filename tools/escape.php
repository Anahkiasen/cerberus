<?php
/*
	Fonction escape
	# Encode une chaîne pour insértion dans une base de données
	
	Fonction html
	# Décode une chaîne encodée pour affichage au sein d'une page
	
	$string
		La chaîne à encoder/décoder
*/
function escape($string)
{
	if(ctype_digit($string)) $string = intval($string);
	else
	{
		$string = mysql_real_escape_string($string);
		$string = addcslashes($string, '%_');
	}
	return $string;
}
function html($string)
{
	$string = htmlspecialchars($string);
	return $string = stripslashes($string);
}
?>