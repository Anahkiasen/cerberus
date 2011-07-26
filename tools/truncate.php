<?php
/*
	Fonction truncate
	# Tronque une cha�ne
	
	$string
		Cha�ne � tronquer
	$count
		Nombre de caract�res/mots/phrases � laisser
	$trailing
		Cha�ne � utiliser pour marquer la c�sure
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