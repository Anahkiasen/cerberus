<?php
/*
	Fonction truncate
	# Tronque une chaîne
	
	$string
		Chaîne à tronquer
	$count
		Nombre de caractères/mots/phrases à laisser
	$mode
		WORD		Tronque après X mots
		SENTENCE	Tronque après X phrases
		DEFAULT		Tronque après X caractères
	$trailing
		Chaîne à utiliser pour marquer la césure
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