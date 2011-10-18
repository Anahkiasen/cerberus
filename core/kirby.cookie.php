<?php
class cookie
{
	// Créer un cookie
	function set($key, $value, $expires = 3600, $domain='/')
	{
		if(is_array($value)) $value = a::json($value);
		$_COOKIE[$key] = $value;
		return @setcookie($key, $value, time()+$expires, $domain);
	}

	// Récupérer un cookie
	function get($key, $default = NULL)
	{
		return a::get($_COOKIE, $key, $default);
	}
	
	// Supprimer un cookie
	function remove($key, $domain='/')
	{
		$_COOKIE[$key] = false;
		return @setcookie($key, false, time()-3600, $domain);
	}
}
?>