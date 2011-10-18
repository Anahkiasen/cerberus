<?php
class server
{
	// Récupère une variable serveur
	static function get($key, $default = NULL)
	{
		if(empty($key)) return $_SERVER;
		return a::get($_SERVER, str::upper($key), $default);
	}
}
?>