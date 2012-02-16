<?php
/** The Kirby Cookie Class
 * This class makes cookie handling easy */
class cookie
{
	// Créer un nouveau cookie
	static function set($key, $value, $expires = 3600, $domain = '/')
	{
		if(is_array($value)) $value = a::json($value);
		$_COOKIE[$key] = $value;
		return @setcookie($key, $value, time() + $expires, $domain);
	}
	
	// Récupère la valeur d'un cookie
	static function get($key, $default = null)
	{
		return a::get($_COOKIE, $key, $default);
	}

	// Supprime un cookie
	static function remove($key, $domain = '/')
	{
		$_COOKIE[$key] = false;
		return @setcookie($key, false, time() - 3600, $domain);
	}

}
?>