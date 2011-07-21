<?php
/*
	Fonction truncate
	# Tronque une cha�ne
	
	$String
		Cha�ne � tronquer
	$length
		Nombre de caract�res � laisser
	$trailing
		Cha�ne � utiliser pour marquer la c�sure
*/
function truncate($string, $length = 255, $trailing = '...')
{
	$length -= mb_strlen($trailing);
	if(mb_strlen($string) > $length)  return mb_substr($string, 0, $length).$trailing;
	else return $string;
}
?>