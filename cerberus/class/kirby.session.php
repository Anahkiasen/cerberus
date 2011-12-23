<?php
class s
{
	// Fonctions d'intéractions
	static function set($key, $value = false)
	{
		if(!isset($_SESSION)) return false;
		if(is_array($key)) $_SESSION = array_merge($_SESSION, $key);
		else $_SESSION[$key] = $value;
	}

	static function get($key = false, $default = NULL)
	{
		if(!isset($_SESSION)) return false;
		if(empty($key)) return $_SESSION;
		return a::get($_SESSION, $key, $default);
	}

	static function remove($key)
	{
		if(!isset($_SESSION)) return false;
		$_SESSION = a::remove($_SESSION, $key, true);
		return $_SESSION;
	}

	// Gestion de la session
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

	// Fonctions utilitaires
	static function expired($time)
	{
		$elapsed_time = (time() - $time);
		return ($elapsed_time >= 0 && $elapsed_time <= config::get('session.expires')) ? FALSE : TRUE;
	}
}
?>