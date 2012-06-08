<?php
/**
 * The Kirby Session Class
 * Handles all session fiddling
 */
class session
{
	/* Returns the current session id */
	public static function id()
	{
		return @session_id();
	}

	/* Sets a session value by key */
	public static function set($key, $value = false)
	{
		if(!isset($_SESSION)) return false;
		if(is_array($key)) $_SESSION = array_merge($_SESSION, $key);
		else $_SESSION[$key] = $value;
	}

	/* Gets a session value by key */
	public static function get($key = false, $default = null)
	{
		if(!isset($_SESSION)) return false;
		if(empty($key)) return $_SESSION;
		return a::get($_SESSION, $key, $default);
	}

	/* Removes a value from the session by key */
	public static function remove($key)
	{
		if(!isset($_SESSION)) return false;
		$_SESSION = a::remove($_SESSION, $key, true);
		return $_SESSION;
	}

	/* Starts a new session */
	public static function start()
	{
		@session_start();
	}

	/* Destroys a session */
	public static function destroy()
	{
		@session_destroy();
	}

	/* Destroys a session first and then starts it again */
	public static function restart()
	{
		self::destroy();
		self::start();
	}
}
