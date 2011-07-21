<?php
/*
	Fonction checkMail
	# Vérifie l'authenticité d'une adresse email donnée
	
	$email
		Adresse à vérifier
	-------------------------------------------------------
	Fonction checkPhone
	# Vérifie l'authenticité d'un numéro de téléphone donné
	
	$phone
		Numéro à vérifier

*/
function checkMail($email)
{
	if(!empty($email) and preg_match("#^[a-z0-9._-]+@[a-z0-9._-]{2,}\.[a-z]{2,4}$#", $email)) return true;
	else return false;
}
function checkPhone($phone)
{
	if(!empty($phone) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $phone)) return true;
	else return false;
}

?>