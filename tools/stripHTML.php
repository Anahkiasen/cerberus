<?php
/*
	Fonction stripHTML
	# Retire le HTML d'un texte
	
	$string
		La chaîne à nettoyer
	$removeBreaks
		Retirer ou non les retours à la ligne
*/
function stripHTML($string, $removeBreaks = false) 
{
	$string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
	$string = strip_tags($string);

	if($removeBreaks)
		$string = preg_replace('/[\r\n\t ]+/', ' ', $string);

	return trim($string);
}
?>