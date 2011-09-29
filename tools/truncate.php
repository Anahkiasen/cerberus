<?php
/*
	Fonction truncate
	# Tronque une cha�ne
	
	$string
		Cha�ne � tronquer
	$count
		Nombre de caract�res/mots/phrases � laisser
	$mode
		WORD		Tronque apr�s X mots
		SENTENCE	Tronque apr�s X phrases
		DEFAULT		Tronque apr�s X caract�res
	$trailing
		Cha�ne � utiliser pour marquer la c�sure
*/
function truncate($string, $count = 255, $mode = NULL, $trailing = NULL)
{
	switch($mode)
	{			
		case 'word':
			preg_match('/^([^.!?\s]*[\.!?\s]+){0,'. $count .'}/', strip_tags($string), $excerpt);
			return $excerpt[0].$trailing;
			break;
			
		case 'sentence':
			preg_match('/^([^.!?]*[\.!?]+){0,'. $count .'}/', strip_tags($string), $excerpt);
			return $excerpt[0].$trailing;
			break;
			
		default:
			$count -= mb_strlen($trailing);
			if(mb_strlen($string) > $count)  return mb_substr($string, 0, $count).$trailing;
			else return $string;
			break;
	}
}
?>