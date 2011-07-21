<?php
/*
	Fonction checkMail
	# V�rifie l'authenticit� d'une adresse email donn�e
	
	$email
		Adresse � v�rifier
	-------------------------------------------------------
	Fonction checkPhone
	# V�rifie l'authenticit� d'un num�ro de t�l�phone donn�
	
	$phone
		Num�ro � v�rifier

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