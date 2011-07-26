<?php
/*
	Fonction checkString
	# V�rifie l'authenticit� d'une cha�ne donn�e
	
	$string
		Cha�ne � v�rifier
	$type
		Type de cha�ne, peut �tre [email, phone]

*/
function checkString($string, $type = 'email')
{
	if($type == 'email')
		return (!empty($string) and preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string));

	elseif($type == 'phone')
		return (!empty($string) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $string));
}
?>