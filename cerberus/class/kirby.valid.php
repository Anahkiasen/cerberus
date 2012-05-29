<?php
class v
{
	/*
	Fonction check
	# Vérifie l'authenticité d'une chaîne donnée

	$string
		Chaîne à vérifier
	$type
		Type de chaîne, peut être une adresse email, un numéro
		de téléphone, un nom ou un chiffre.
		Dans tous les cas vérifie si la chaîne n'est pas vide.
	*/
	static function check($string, $type)
	{
		switch($type)
		{
			case 'facultative':
				return true;
				break;

			case 'url':
				return !empty($string) and v::url($string);
				break;

			case 'email':
				return !empty($string) and v::email($string);
				break;

			case 'phone':
			case 'telephone':
				return !empty($string) and v::phone($string);
				break;

			case 'nom':
			case 'prenom':
			case 'name':
				return !empty($string) and preg_match('/\D+/', $string);
				break;

			case 'number':
				return !empty($string) and preg_match('/\d+/', $string);

			default:
				return !empty($string);
				break;
		}
	}

	/*
	########################################
	########### FONCTIONS MOTEUR ###########
	########################################
	*/

	/* Core method to create a new validator */
	static function string($string, $options)
	{
		$format = null;
		$min_length = $max_length = 0;
		if(is_array($options)) extract($options);

		if($format && !preg_match('/^[$format]*$/is', $string)) return false;
		if($min_length && str::length($string) < $min_length) return false;
		if($max_length && str::length($string) > $max_length) return false;
		return true;
	}

	/* Checks for a valid password */
	static function password($password)
	{
		return self::string($password, array('min_length' => 4));
	}

	/* Checks for two valid, matching password */
	static function passwords($password1, $password2)
	{
		return ($password1 == $password2 && self::password($password1) && self::password($password2));
	}

	/* Checks for valid date */
	static function date($date)
	{
		$time = strtotime($date);
		if(!$time) return false;

		$year = date('Y', $time);
		$month = date('m', $time);
		$day = date('d', $time);

		return (checkdate($month, $day, $year)) ? $time : false;
	}

	/* Checks for valid email address */
	static function email($email)
	{
		$regex = '#^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$#ix';
		return (preg_match($regex, $email));
	}

	/* Checks for valid URL */
	static function url($url)
	{
		$regex = '/^(https?|ftp|rmtp|mms|svn):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i';
		return (preg_match($regex, $url));
	}

	/* Checks for valid filename */
	static function filename($string)
	{
		$options = array('format' => 'a-zA-Z0-9_-', 'min_length' => 2);
		return self::string($string, $options);
	}

	/**** Vérifie qu'un numéro de téléphone est valide */
	static function phone($phone)
	{
		$regex = '#^[\d \+\(\)\-]+$#';
		return (preg_match($regex, $phone));
	}
}
