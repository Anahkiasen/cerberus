<?php
/**
 * 
 * Server
 * 
 * Makes it more convenient to get variables
 * from the global server array
 * 
 * @package Kirby
 */
class server
{
	/**
		* Gets a value from the _SERVER array
		*
		* @param	mixed		$key The key to look for. Pass false or null to return the entire server array. 
		* @param	mixed		$default Optional default value, which should be returned if no element has been found
		* @return mixed
		*/	
	static function get($key = FALSE, $default = NULL)
	{
		if(empty($key)) return $_SERVER;
		return a::get($_SERVER, str::upper($key), $default);
	}
}
?>