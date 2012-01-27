<?php
class v
{
	function check($string, $type = NULL)
	{
		switch($type)
		{
			case 'email':
				return (!empty($string) and self::email($string));
				break;
			
			case 'url':
				return (!empty($string) and self::url($string));
				break;
			
			case 'phone':
			case 'telephone':
				return (!empty($string) and self::phone($string));
				break;
			
			case 'name':
			case 'nom':
			case 'prenom':
				return (!empty($string) and preg_match("#^\D+$#", $string));
				break;
			
			case 'number':
				return (!empty($string) and preg_match("#^\d+$#", $string));
				break;
				
			case 'facultative':
				return true;
				break;
			
			default:
				return (!empty($string));
		}
	}
	
	/*
	########################################
	#### VERIFICATIONS INDIVIDUELLES #######
	######################################## 
	*/
	
	// Vérifie qu'un numéro de téléphone est valide
	static function phone($phone)
	{
		$regex = '#^0[1-78]([-. ]?[0-9]{2}){4}$#';
		return (preg_match($regex, $phone));
	}
	
	// Vérifie qu'une email est valide
	static function email($email)
	{
		$regex = '#^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$#ix';
		return (preg_match($regex, $email));
	}

	// Vérifie qu'une URL est valide
	static function url($url)
	{
		$regex = '/^(https?|ftp|rmtp|mms|svn):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i';
		return (preg_match($regex, $url));
	}

	// Vérifie qu'une date est valide
	static function date($date)
	{
		$time = strtotime($date);
		if(!$time)
			return false;

		$year = date('Y', $time);
		$month = date('m', $time);
		$day = date('d', $time);

		return (checkdate($month, $day, $year)) ? $time : false;
	}

	// Vérifie qu'un nom de fichier est valide
	static function filename($string)
	{
		$options = array('format' => 'a-zA-Z0-9_-', 'min_length' => 2, );
		return self::string($string, $options);
	}

	// Vérifie qu'une chaîne est valide
	static function string($string, $options)
	{
		$format = null;
		$min_length = $max_length = 0;
		if(is_array($options))
			extract($options);

		if($format && !preg_match('/^[$format]*$/is', $string))
			return false;
		if($min_length && str::length($string) < $min_length)
			return false;
		if($max_length && str::length($string) > $max_length)
			return false;
		return true;
	}

	// Vérifie un mot de passe
	static function password($password)
	{
		return self::string($password, array('min_length' => 4));
	}

	// Vérifie que deux mots de passe concordent
	static function passwords($password1, $password2)
	{
		return ($password1 == $password2 && self::password($password1) && self::password($password2));
	}
}
?>