<?php
class config
{
	private static $config = array();
	
	// Charger un fichier config
	static function load($file)
	{
		if(file_exists($file)) require_once($file);
		if(isset($config)) self::set($config);
		return self::get();
	}
	
	// Récupérer une valeur config
	static function get($key = NULL, $default = NULL)
	{
		if(empty($key)) return self::$config;
		return a::get(self::$config, $key, $default);
	}
	
	// Changer une valeur config
	static function set($key, $value = NULL)
	{
		if(is_array($key)) self::$config = array_merge(self::$config, $key);
		else self::$config[$key] = $value;
	}
}
?>