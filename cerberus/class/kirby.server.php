<?php
/* The Kirby Server Getter Class */
class server
{
	// Gets a value from the _SERVER array
	static function get($key = false, $default = NULL)
	{
		if(empty($key)) return $_SERVER;
		return a::get($_SERVER, str::upper($key), $default);
	}
}
?>