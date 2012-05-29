<?php
/**
 *
 * Cookie
 *
 * This class makes cookie handling easy
 *
 * @package Kirby
 */
class cookie
{
	/**
		* Set a new cookie
		*
		* @param	string	$key The name of the cookie
		* @param	string	$value The cookie content
		* @param	int		 $expires The number of seconds until the cookie expires
		* @param	string	$domain The domain to set this cookie for.
		* @return boolean true: the cookie has been created, false: cookie creation failed
		*/
	static function set($key, $value, $expires = 3600, $domain = '/')
	{
		if(is_array($value)) $value = a::json($value);
		$_COOKIE[$key] = $value;
		return @setcookie($key, $value, (time() + $expires), $domain);
	}

	/**
		* Get a cookie value
		*
		* @param	string	$key The name of the cookie
		* @param	string	$default The default value, which should be returned if the cookie has not been found
		* @return mixed	 The found value
		*/
	static function get($key, $default = NULL)
	{
		return a::get($_COOKIE, $key, $default);
	}

	/**
		* Remove a cookie
		*
		* @param	string	$key The name of the cookie
		* @param	string	$domain The domain of the cookie
		* @return mixed	 true: the cookie has been removed, false: the cookie could not be removed
		*/
	static function remove($key, $domain = '/')
	{
		$_COOKIE[$key] = false;
		return @setcookie($key, false, (time() - 3600), $domain);
	}
}
