<?php
/*
	Fonction checkString
	# Vérifie l'authenticité d'une chaîne donnée
	
	$string
		Chaîne à vérifier
	$type
		Type de chaîne, peut être une adresse email, un numéro
		de téléphone, un nom ou un chiffre.
		Dans tous les cas vérifie si la chaîne n'est pas vide.
*/
function checkString($string, $type = NULL)
{
	switch($type)
	{
		case 'email':
		return (!empty($string) and preg_match("#^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$#ix", $string));
		break;
		
		case 'phone':
		case 'telephone':
		return (!empty($string) and preg_match("#^0[1-78]([-. ]?[0-9]{2}){4}$#", $string));
		break;
		
		case 'name':
		case 'nom':
		case 'prenom':
		return (!empty($string) and preg_match("#^[-' a-zA-Z]+$#", $string));
		break;
		
		case 'number':
		return (!empty($string) and preg_match("#^\d+$#", $string));
		break;
		
		default:
		return (!empty($string));
	}
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
	
	$mailbody = NULL;
	
	// Liste des champs voulus et incomplets
	$funcGet = func_get_args();
	foreach($funcGet as $id => $champ)
	{
		if(is_array($champ)) $fields[key($champ)] = $champ[key($champ)];
		else $fields[$champ] = $champ;
	}
	$unfilled = array_keys($fields);
	$misfilled = array();
	
	$erreurs = array();
	$multilangue = (isset($index['fr']['form-erreur-email']));

	// Lecture des données
	foreach($_POST as $key => $value)
	{
		if(!empty($value) and isset($fields[$key]))
		{
			$unfilled = array_diff($unfilled, array($key));
			if(checkString($value, $fields[$key]))
			{	
				if($multilangue) $mailbody .= '<strong>' .index('form-' .$key). '</strong> : ' .$value. '<br />';
			}
			else $misfilled[] = $key;
		}
	}
	
	// On vérifie que les champs sont remplis
	$isUnfilled = ($multilangue)
		? index('form-erreur-incomplete')
		: 'Un ou plusieurs champs sont incomplets';
	$isMisfilled = ($multilangue)
		? index('form-erreur-incorrect')
		: 'Un ou plusieurs champs sont incorrects';
		
	$typesErreur = array('un', 'mis');
	foreach($typesErreur as $erreur)
	{
		$variable = ${$erreur. 'filled'};
		if(isset($variable) and !empty($variable))
		{
			if($multilangue) foreach($variable as $key => $value) $variable[$key] = index('form-' .$value);
			else foreach($variable as $key => $value) $variable[$key] = ucfirst($value);
			$erreurs[] = ${'is' .ucfirst($erreur). 'filled'}. ' : ' .implode(', ', $variable);
		}
	}

	// Affiche des possibles erreurs, sinon validation	
	if(!empty($erreurs))
	{
		echo display(implode('<br />', $erreurs));
		return false;
	}
	else return ($multilangue) ? $mailbody : true;
}
?>