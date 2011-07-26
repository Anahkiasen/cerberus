<?php
/*
	Fonction truncate
	# Tronque une chaîne
	
	$string
		Chaîne à tronquer
	$count
		Nombre de caractères/mots/phrases à laisser
	$trailing
		Chaîne à utiliser pour marquer la césure
*/
function truncate($string, $count = 255, $trailing = '')
{
	$length -= mb_strlen($trailing);
	if(mb_strlen($string) > $count)  return mb_substr($string, 0, $count).$trailing;
	else return $string;
}
function truncateSentences($string, $count) 
{
	preg_match('/^([^.!?]*[\.!?]+){0,'. $count .'}/', strip_tags($string), $excerpt);
	return $excerpt[0];
}
function truncateWords($string, $count) 
{
	preg_match('/^([^.!?\s]*[\.!?\s]+){0,'. $count .'}/', strip_tags($string), $excerpt);
	return $excerpt[0];
}
?>