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
		return (!empty($string) and preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix", $string));

	elseif($type == 'phone')
		return (!empty($string) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $string));
}
/*
	Fonction checkFields
	# Vérifie si un formulaire a été correctement rempli
	
	$fields
		Liste des champs obligatoires
*/
function checkFields()
{
	global $index;
	
	$fields = func_get_args();
	$filled = $fields;
	$erreurs = array();
	$multilangue = (isset($index['fr']['form-erreur-email']));

	foreach($_POST as $key => $value)
		if(!empty($value) and in_array($key, $fields)) $filled = array_diff($filled, array($key));
	
	// On vérifie que les champs sont remplis
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

	// Vérification de la validité des informations
	if($multilangue == true)
	{
		if(in_array('email', $fields)) if(!empty($_POST['email']) and !checkString($_POST['email'])) $erreurs[] = index('form-erreur-email');
		if(in_array('phone', $fields)) if(!empty($_POST['phone']) and !checkString($_POST['phone'], 'phone')) $erreurs[] = index('form-erreur-phone');
	}
	else
	{
		if(in_array('email', $fields)) if(!empty($_POST['email']) and !checkString($_POST['email'])) $erreurs[] = 'Adresse email non valide';
		if(in_array('telephone', $fields)) if(!empty($_POST['telephone']) and !checkString($_POST['telephone'], 'phone')) $erreurs[] = 'Numéro de téléphone non valide';
	}
	
	// Affiche des possibles erreurs, sinon validation	
	if(!empty($erreurs))
	{
		echo display(implode('<br />', $erreurs));
		return false;
	}
	else return true;
}
?>