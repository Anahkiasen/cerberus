<?php
/*
	Fonction truncate
	# Tronque une chaîne
	
	$String
		Chaîne à tronquer
	$length
		Nombre de caractères à laisser
	$trailing
		Chaîne à utiliser pour marquer la césure
*/
function truncate($string, $length = 255, $trailing = '...')
{
	$length -= mb_strlen($trailing);
	if(mb_strlen($string) > $length)  return mb_substr($string, 0, $length).$trailing;
	else return $string;
}
?>