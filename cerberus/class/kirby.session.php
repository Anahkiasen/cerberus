<?php
/* The Kirby Session Class
 * Handles all session fiddling */
class s
{
	// Retourn l'ID de la session en cours
	static function id()
	{
		return @session_id();
	}
	
	// Ajoute un élément de session
	static function set($key, $value = false)
	{
		if(!isset($_SESSION)) return false;
		if(is_array($key)) $_SESSION = array_merge($_SESSION, $key);
		else $_SESSION[$key] = $value;
	}
	
	// Récupère un élément de la session en cours
	static function get($key = false, $default = null)
	{
		if(!isset($_SESSION)) return false;
		if(empty($key)) return $_SESSION;
		return a::get($_SESSION, $key, $default);
	}
	
	// Supprime un élément de la session en cours
	static function remove($key)
	{
		if(!isset($_SESSION)) return false;
		$_SESSION = a::remove($_SESSION, $key, true);
		return $_SESSION;
	}
	
	// Gestion de la session en cours
	static function start()
	{
		@session_start();
	}
	
	static function destroy()
	{
		@session_destroy();
	}
	
	static function restart()
	{
		self::destroy();
		self::start();
	}
}
?>