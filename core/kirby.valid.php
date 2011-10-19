<?php
class v
{
	// Vérifie qu'une email est valide
	function email($email)
	{
		$regex = '/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i';
		return (preg_match($regex, $email));
	}

	// Vérifie qu'une URL est valide
	function url($url)
	{
		$regex = '/^(https?|ftp|rmtp|mms|svn):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)(:(\d+))?\/?/i';
		return (preg_match($regex, $url));
	}

	// Vérifie qu'une date est valide
	function date($date)
	{
		$time = strtotime($date);
		if(!$time) return false;

		$year	= date('Y', $time);
		$month	= date('m', $time);
		$day	= date('d', $time);

		return (checkdate($month, $day, $year)) ? $time : false;
	}
	
	// Vérifie qu'un nom de fichier est valide
	function filename($string)
	{
		$options = array(
			'format'	 => 'a-zA-Z0-9_-',
			'min_length' => 2,
		);

		return self::string($string, $options);
	}
	
	// Vérifie qu'une chaîne est valide
	function string($string, $options)
	{
		$format = NULL;
		$min_length = $max_length = 0;
		if(is_array($options)) extract($options);

		if($format && !preg_match('/^[$format]*$/is', $string)) 	return false;
		if($min_length && str::length($string) < $min_length) 		return false;
		if($max_length && str::length($string) > $max_length)	 	return false;
		return true;
	}







	function password($password)
	{
		return self::string($password, array('min_length' => 4));
	}

	function passwords($password1, $password2)
	{

		if($password1 == $password2
			&& self::password($password1)
			&& self::password($password2))
			{
			return true;
		} 
		else  return false;
	}
}
?>