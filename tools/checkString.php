<?php
/*
	Fonction checkString
	# Vérifie l'authenticité d'une chaîne donnée
	
	$string
		Chaîne à vérifier
	$type
		Type de chaîne, peut être [email, phone]

*/
function checkString($string, $type = 'email')
{
	if($type == 'email')
	{
		if(!empty($string) and preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $string)) return true;
		else return false;
	}
	elseif($type == 'phone')
	{
		if(!empty($string) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $string)) return true;
		else return false;
	}
}
?>