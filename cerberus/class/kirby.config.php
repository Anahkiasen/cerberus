<?php
/**
 *
 * Config
 *
 * This is the core class to handle
 * configuration values/constants.
 *
 * @package Kirby
 */
class config
{
	/**
	 * The static config array (contains all config values)
	 * @var array
	 */
	private static $config = array();

	/**
	 * The path to the main config file
	 * @var string
	 */
	private static $config_file = PATH_CONF;

	/**
	 * The default values for most configuration options
	 * @var array
	 */
	 public static $defaults = array
	 (
		'db.charset'          => 'utf8',

		/* Errors */
		'developper.mail'     => 'maxime@stappler.fr',

		/* Modules */
		'bootstrap'           => true,
		'compass'             => array('susy', 'animate', 'rgbapng', 'modular-scale', 'normalize'),
		'logs'                => false,
		'minify'              => true,

		/* Options */
		'cache'               => true,
		'cache.manifest'      => false,
		'local'               => false,
		'meta'                => false,
		'multilangue'         => false,
		'rewriting'           => false,
		'uasniff'             => false,

		/* Upload */
		'upload.allowed'      => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
		'upload.overwrite'    => true,

		/* MySQL */
		'admin.login'         => 'root',
		'admin.password'      => 'root',
		'local.name'          => false,

		/* Cache */
		'cache.get_variables' => true,
		'cache.time'          => null,

		/* Navigation */
		'index'               => 'index');

	/**
	 * Gets a config value by key
	 *
	 * @param  string  $key      The key to look for. Pass false to get the entire config array
	 * @param  mixed   $default  The default value, which will be returned if the key has not been found
	 * @return mixed   The found config value
	 */
	public static function get($key = null, $default = null)
	{
		if(empty($key)) return self::$config;
		return a::get(self::$config, $key, $default);
	}

	/**
	 * Sets a config value by key
	 *
	 * @param string	 $key The key to define
	 * @param mixed	 $value The value for the passed key
	 */
	public static function set($key, $value = null)
	{
		if(is_array($key)) self::$config = array_merge(self::$config, $key);
		else self::$config[$key] = $value;
	}

	/**
	 * Loads an additional config file
	 * Returns the entire configuration array
	 *
	 * @param  string  $file The path to the config file
	 * @return array   The entire config array
	 */
	public static function load($file)
	{
		if(file_exists($file)) $config = f::read($file, 'json');
		if(isset($config)) self::set($config);
		return self::get();
	}

	/**
	 * Adds a value to the config file
	 *
	 * @param  string  $key The parameter to add
	 * @param  string  $value Its value
	 * @return boolean The success of writing into the file
	 */
	public static function hardcode($key, $value = null)
	{
		$json = f::read(PATH_CONF, 'json');
		$json[$key] = $value;
		f::write(PATH_CONF, $json);
	}

	/**
	 * Updates the current config file with SQL informations
	 *
	 * @param  string $local_name      Local database name
	 * @param  string $online_host     Online host
	 * @param  string $online_user     Online SQL user
	 * @param  string $online_password Online SQL password
	 * @param  string $online_name     Online database
	 */
	public static function mysql(
		$local_name      = null,
		$online_host     = null,
		$online_user     = null,
		$online_password = null,
		$online_name     = null)
	{
		if($local_name and !self::get('local.name')) self::hardcode('local.name', $local_name);
		if(!self::get('db.host') and $online_password and $online_host and $online_name and $online_user)
		{
			self::hardcode('db.host',     $online_host);
			self::hardcode('db.user',     $online_user);
			self::hardcode('db.password', $online_password);
			self::hardcode('db.name',     $online_name);
		}
	}
}

/**
 * Returns the status from a Kirby response
 *
 * @param   array   $response The Kirby response array
 * @return  string  "error" or "success"
 * @package Kirby
 */
function status($response)
{
	return a::get($response, 'status');
}

/**
 * Returns the message from a Kirby response
 *
 * @param   array   $response The Kirby response array
 * @return	string  The message
 * @package Kirby
 */
function msg($response)
{
	return a::get($response, 'msg');
}

/**
 * Checks if a Kirby response is an error response or not.
 *
 * @param   array   $response The Kirby response array
 * @return  boolean	Returns true if the response is an error, returns false if no error occurred
 * @package Kirby
 */
function error($response)
{
	return status($response) == 'error';
}

/**
 * Checks if a Kirby response is a success response.
 *
 * @param   array    $response The Kirby response array
 * @return  boolean  Returns true if the response is a success, returns false if an error occurred
 * @package Kirby
 */
function success($response)
{
	return !error($response);
}
