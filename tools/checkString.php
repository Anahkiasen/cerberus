<?php
/*
	Fonction checkString
	# V�rifie l'authenticit� d'une cha�ne donn�e
	
	$string
		Cha�ne � v�rifier
	$type
		Type de cha�ne, peut �tre une adresse email, un num�ro
		de t�l�phone, un nom ou un chiffre.
		Dans tous les cas v�rifie si la cha�ne n'est pas vide.
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
		return (!empty($string) and preg_match("#^\D+$#", $string));
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
	# V�rifie si un formulaire a �t� correctement rempli
	
	$fields
		Liste des champs obligatoires
		Peut pr�ciser le type d'un champ pour qu'il
		soit reconnu sous la syntaxe CHAMP => TYPE
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

	// Lecture des donn�es
	foreach($_POST as $key => $value)
	{
		if(!empty($value) and isset($fields[$key]))
		{
			$unfilled = array_diff($unfilled, array($key));
			if(checkString($value, $fields[$key]))
			{	
				if(MULTILANGUE) $mailbody .= '<strong>' .index('form-' .$key). '</strong> : ' .stripslashes($value). '<br />';
			}
			else $misfilled[] = $key;
		}
	}
	
	// On v�rifie que les champs sont remplis
	$isUnfilled = (MULTILANGUE)
		? index('form-erreur-incomplete')
		: 'Un ou plusieurs champs sont incomplets';
	$isMisfilled = (MULTILANGUE)
		? index('form-erreur-incorrect')
		: 'Un ou plusieurs champs sont incorrects';
		
	$typesErreur = array('un', 'mis');
	foreach($typesErreur as $erreur)
	{
		$variable = ${$erreur. 'filled'};
		if(isset($variable) and !empty($variable))
		{
			if(MULTILANGUE) foreach($variable as $key => $value) $variable[$key] = index('form-' .$value);
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
	else return (MULTILANGUE) ? $mailbody : true;
}
?>