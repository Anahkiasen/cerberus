<?php
/*
	Fonction escape
	# Encode une cha�ne pour ins�rtion dans une base de donn�es
	
	Fonction html
	# D�code une cha�ne encod�e pour affichage au sein d'une page
	
	$string
		La cha�ne � encoder/d�coder
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