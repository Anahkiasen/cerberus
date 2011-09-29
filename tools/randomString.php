<?php
/*
	Fonction randomString
	# Génère une chaîne aléatoire
	
	$length
		La longueur de la chaîne voulue
*/
function randomString($length = 15)
{
	$password = NULL;
	$possible = '0123456789abcdfghjkmnpqrstvwxyzABCDFGHJKLMNPQRSTVWXYZ'; 
	
	for($i = 0; $i < $length; $i++)		
	{ 
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
		if(!strstr($password, $char)) $password .= $char;
	}
	return $password;
}
?>