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
/*
	Fonction checkFields
	# V�rifie si un formulaire a �t� correctement rempli
	
	$fields
		Liste des champs obligatoires
*/
function checkFields($fields)
{
	$multilangue = false;
	$erreurs = array();
	
	$filled = $fields;
	foreach($_POST as $key => $value)
		if(!empty($value) and in_array($key, $fields)) $filled = array_diff($filled, array($key));
	
	// Si formulaire incomplet
	if(!empty($filled))
	{
		if($multilangue == true)
		{
			foreach($filled as $key => $value) $filled[$key] = index('form-' .$value);
			$erreurs[] = index('form-erreur-incomplete'). ' : ' .implode(', ', $filled);
		}
		else
		{
			foreach($filled as $key => $value) $filled[$key] = ucfirst($value);
			$erreurs[] = 'Un ou plusieurs champs sont incomplets : ' .implode(', ', $filled);
		}
	}

	if($multilangue == true)
	{
		if(in_array('email', $fields)) if(!checkString($_POST['email'])) $erreurs[] = index('form-erreur-email');
		if(in_array('phone', $fields)) if(!checkString($_POST['phone'], 'phone')) $erreurs[] = index('form-erreur-phone');
	}
	else
	{
		if(in_array('email', $fields)) if(!checkString($_POST['email'])) $erreurs[] = 'Adresse email non valide';
		if(in_array('telephone', $fields)) if(!checkString($_POST['telephone'], 'phone')) $erreurs[] = 'Num�ro de t�l�phone non valide';
	}
		
	if(!empty($erreurs)) return display(implode('<br />', $erreurs));
	else return true;
}
?>