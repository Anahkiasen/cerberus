<?php
class s
{
	// Fonctions d'intéractions
	static function set($key, $value = FALSE)
	{
		if(is_array($key)) $_SESSION = array_merge($_SESSION, $key);
		else $_SESSION[$key] = $value;
	}

	static function get($key = FALSE, $default = NULL)
	{
		if(empty($key)) return $_SESSION;
		else return a::get($_SESSION, $key, $default);
	}

	static function remove($key)
	{
		unset($_SESSION[$key]);
		//return a::remove(&$_SESSION, $key, true);
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

	// Fonctions utilitaires
	static function expired($time)
	{
		$elapsed_time = (time() - $time);
		return ($elapsed_time >= 0 && $elapsed_time <= config::get('session.expires')) ? FALSE : TRUE;
	}
}
?>