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
		* The static config array
		* It contains all config values
		* 
		* @var array
		*/
	private static $config = array();
	
	/**
	 * The path to the main config file
	 * 
	 * @var string
	 */
	private static $config_file = PATH_CONF;
	
	/**
	 * The default values for most configuration options
	 * 
	 * @var array
	 */	 
	 public static $defaults = array(
	 	'db.charset' => 'utf8',
	 
	 	/* Errors */
	 	'developper.mail' => 'maxime@stappler.fr',
	 
		/* Modules */
		'bootstrap'   => true,
		'compass'     => array('susy', 'animation', 'rgbapng', 'modular-scale', 'normalize'),
		'logs'        => false,
		'minify'      => true,
		'modernizr'   => false,
		
		/* Options */
		'cache'          => true,
		'cache.manifest' => false,
		'local'          => false,
		'meta'           => false,
		'multilangue'    => false,
		'rewriting'      => false,
		
		/* Upload */
		'upload.allowed'   => array('image/jpeg', 'image/pjpeg', 'image/png', 'image/gif'),
		'upload.overwrite' => true,
		
		/* MySQL */
		'admin.login'    => 'root',
		'admin.password' => 'root',
		'local.name'     => false,
		
		/* Cache */
		'cache.get_variables' => true,
		'cache.time' => null,
		
		/* Navigation */
		'index' => 'index');
 
	/** 
		* Gets a config value by key
		*
		* @param	string	$key The key to look for. Pass false to get the entire config array
		* @param	mixed	 $default The default value, which will be returned if the key has not been found
		* @return mixed	 The found config value
		*/	
	static function get($key = NULL, $default = NULL)
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
	static function set($key, $value = NULL)
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
	static function load($file)
	{
		if(file_exists($file)) require_once($file);
		if(isset($config)) self::set($config);
		return self::get();
	}
	
	/**
	 * Adds a value to the config file
	 * 
	 * @param  string 	$key The parameter to add
	 * @param  string 	$value Its value
	 * @return boolean 	The success of writing into the file
	 */
	static function hardcode($key, $value = NULL)
	{
		// Traitement de la valeur
		if(is_array($value)) $value = 'array(\'' .implode("', '", $formatted_value). '\')';
		elseif(is_bool($value)) $value = str::boolprint($value);
		elseif(is_null($value)) $value = 'NULL';
		else $value = '"' .$value. '"';
		
		$config = f::read(self::$config_file);
		
		// Recherche de sa présence dans le fichier config
		if(preg_match('#\$config\[\'(' .$key. ')\'\] = (.+);#', $config))
		{
			$config = preg_replace(
				'#\$config\[\'(' .$key. ')\'\] = (.+);#',
				'$config[\'$1\'] = ' .$value. ';',
				$config);
		}
		else if(!empty($value)) $config = str_replace('?>', '$config[\'' .$key. '\'] = ' .$value. ";\n?>", $config);
		
		return f::write(self::$config_file, $config);
	}
	
	/**
	 * Updates the current config file with MySQL informations
	 */
	static function mysql($local_name = NULL, $online_host = NULL, $online_user = NULL, $online_password = NULL, $online_name = NULL)
	{
		if($local_name and !self::get('local.name')) self::hardcode('local.name', $local_name);
		if(!self::get('db.host') and $online_password and $online_host and $online_name and $online_user)
		{
			self::hardcode('db.host', $online_host);
			self::hardcode('db.user', $online_user);
			self::hardcode('db.password', $online_password);
			self::hardcode('db.name', $online_name);
		}
	}
}

/**
 * Returns the status from a Kirby response
 *
 * @param	 array	$response The Kirby response array
 * @return	string	"error" or "success"
 * @package Kirby
 */
function status($response)
{
	return a::get($response, 'status');
}

/**
 * Returns the message from a Kirby response
 *
 * @param	 array		$response The Kirby response array
 * @return	string	 The message
 * @package Kirby
 */
function msg($response)
{
	return a::get($response, 'msg');
}

/**
 * Checks if a Kirby response is an error response or not. 
 *
 * @param	 array		$response The Kirby response array
 * @return	boolean	Returns true if the response is an error, returns false if no error occurred 
 * @package Kirby
 */
function error($response)
{
	return status($response) == 'error';
}

/**
 * Checks if a Kirby response is a success response. 
 *
 * @param	 array		$response The Kirby response array
 * @return	boolean	Returns true if the response is a success, returns false if an error occurred
 * @package Kirby
 */
function success($response)
{
	return !error($response);
}
?>