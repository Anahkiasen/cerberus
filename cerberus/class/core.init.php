<?php
/**
 *
 * init
 *
 * This class loads the different parts of Cerberus
 *
 * @package Cerberus
 */
class init
{
	/**
	 * Store the currently loaded modules
	 * @var array
	 */
	private static $modules = array();

	/**
	 * List the different dependencies
	 * @var array
	 */
	private static $dependencies = array(
		'autoloader'  => 'paths',
		'cache'       => 'navigation,constants',
		'config'      => 'paths',
		'constants'   => 'config,paths',
		'debug'       => 'constants,autoloader',
		'dispatch'    => 'autoloader,constants,paths,config',
		'errorHandle' => 'paths',
		'mysql'       => 'constants,config',
		'required'    => 'dispatch,paths',
		'stats'       => 'config,mysql,constants',
		);

	/**
	 * Initializes the base modules
	 */
	function __construct()
	{
		// Correct headers as soon as launched, if possible
		$this->correctHeader();

		// Tweak some base PHP parameters
		$this->iniPHP();

		// Setting main path constants
		$this->paths();

		// Defining and assigning class __autoloader
		$this->autoloader();

		// Set main language of the environnement
		$this->strings();

		// Error handling
		$this->errorHandling();

		// Timezone setting
		$this->timezone();

		// Starting a new session
		session::start();
	}

	//////////////////////////////////////////////////////////////
	/////////////////////////// HELPERS //////////////////////////
	//////////////////////////////////////////////////////////////

	/**
	 * Initiate a module and check for its dependencies
	 *
	 * @param  string   $module        The module to load
	 * @param  array    $dependencies  A list of the required modules
	 * @return boolean  Module correctly loaded or not
	 */
	private function module($module)
	{
		// If the module has dependencies
		if(isset(self::$dependencies[$module]))
		{
			 $dependencies = self::$dependencies[$module];

			// Assure the dependencies are an array
			if(!is_array($dependencies)) $dependencies = explode(',', $dependencies);

			// Check if all dependencies are loaded
			$dependenciesLoaded = self::dependencies($dependencies);
			$allLoaded = (sizeof($dependenciesLoaded) == sizeof($dependencies));

			// Throws an error if the required modules are not found
			if(!$allLoaded and !empty($dependencies))
			{
				echo 'Erreur lors du chargement du module ' .$module. '<br />
				Les dépendences suivantes n\'ont pas été chargées : ' .implode(', ', array_diff($dependencies, $dependenciesLoaded));
				exit();
			}
		}
		else $allLoaded = true;

		// Setting the current module as loaded
		self::$modules[$module] = true;

		// Returns a state of the module loading
		return $allLoaded;
	}

	/**
	 * Checks if the dependencies of the current module are all loaded
	 *
	 * @param  array    $dependencies A list of the required modules
	 * @return boolean  Whether or not the listed dependencies were all loaded
	 */
	private function dependencies($dependencies = array())
	{
		// Creating an array to put the loaded dependencies in
		$dependenciesLoaded = array();

		// Iterate through the dependencies list, checking if they're all loaded
		foreach($dependencies as $d)
			if(self::loaded($d)) $dependenciesLoaded[] = $d;

		// If the number of loaded modules doesn't match the list, return false
		return $dependenciesLoaded;
	}

	/**
	 * Checks if a module is correctly loaded
	 *
	 * @param  string   $module The module to check
	 * @return boolean  Loaded or not
	 */
	private function loaded($module)
	{
		return isset(self::$modules[$module]) and self::$modules[$module];
	}

	//////////////////////////////////////////////////////////////
	/////////////////////////// MODULES //////////////////////////
	//////////////////////////////////////////////////////////////

	/**
	 * Correct the base encoding if possible
	 */
	function correctHeader()
	{
		if (!headers_sent())
			header('Content-Type: text/html; charset=utf-8');
	}

	/**
	 * Overwrite some of the base PHP parameters
	 */
	function iniPHP()
	{
		ini_set('upload_max_filesize', '100M');
		ini_set('default_charset', 'utf-8');
	}

	/**
	 * Defines the current timezone
	 */
	function timezone()
	{
		self::module('timezone');
		date_default_timezone_set('Europe/Paris');
	}

	/**
	* Defines the paths to some of the main folders
	*/
	function paths()
	{
		self::module('paths');

		// Define PATH_MAIN (root folder)
		if(!defined('PATH_MAIN')) define('PATH_MAIN', NULL);

		// Define PATH_CORE (cerberus folder)
		if(!defined('PATH_CORE')) define('PATH_CORE', PATH_MAIN.'cerberus/');

		// Define PATH_CONF (conf.json file)
		if(!defined('PATH_CONF')) define('PATH_CONF', PATH_CORE.'conf.json');

		// Define PATH_CACHE (cerberus cache folder)
		if(!defined('PATH_CACHE')) define('PATH_CACHE', PATH_CORE.'cache/');
	}

	/**
	* Handles and log errors
	* @dependencies		paths
	*/
	function errorHandling()
	{
		self::module('errorHandling');

		// If DEPRECATED is not defined
		if(!defined('E_DEPRECATED')) define('E_DEPRECATED', 8192);

		// Loading errorHandle
		include(PATH_CORE.'tools/errorHandle.php');
		set_error_handler('errorHandle');
		set_exception_handler(array('Debug', 'handle'));

		// Setting error level
		error_reporting(E_ALL | E_STRICT ^ E_DEPRECATED);

		// Error logging
		ini_set('error_log', PATH_CACHE.'error.log');
		ini_set('log_errors', 'On');
	}

	/**
	* Assign autoloader as class loader
	* @dependencies		paths
	*/
	function autoloader()
	{
		self::module('autoloader');

		// Include the Autoloader and set it as main loader
		include(PATH_CORE.'tools/classloader.php');
		spl_autoload_register('__autoLoader');
	}

	/**
	* Load the configuration file and set default values
	* @dependencies		paths
	*/
	function config()
	{
		self::module('config');

		// Load the configuration default's value
		config::set(config::$defaults);

		// Create the config file if it doesn't exist
		if(!file_exists(PATH_CONF)) f::write(PATH_CONF, '<?'.PHP_EOL.'?>');

		// Load the local configuration file
		else config::load(PATH_CONF);
	}

	/**
	 * Loads the Cerberus language file containing errors and messages
	 */
	function strings()
	{
		l::locale('fr');
		l::load('cerberus/include/cerberus.{langue}.json');
	}

	/**
	* Define some of the main constants
	* @dependencies		config,paths
	*/
	function constants()
	{
		self::module('constants');

		// Page is local or not
		if(!defined('LOCAL'))
			define('LOCAL', server::local());

		// Setting parameters according to local or production
		config::set(array(
			'minify'    => !LOCAL,
			'cache'     => !LOCAL,
			'rewriting' => !LOCAL,
			'local'     =>  LOCAL));

		// Main constants
		if(!defined('REWRITING'))   define('REWRITING',   config::get('rewriting'));
		if(!defined('MULTILANGUE')) define('MULTILANGUE', config::get('multilangue'));
		if(!defined('CACHE'))
		{
			if(LOCAL or PATH_MAIN != NULL) define('CACHE', false); // If we're in local or in a subfolder
			else                           define('CACHE', config::get('cache'));
		}
	}

	/**
	* Connects to the configured database
	* @dependencies		constants, config
	*/
	function mysql()
	{
		self::module('mysql');

		// If we have database names to use
		if(config::get('local.name') or config::get('online.name'))
		{
			// If local, we set the SQL login informations to the local.variables
			if(LOCAL) config::set(array(
				'db.host'     => config::get('local.host'),
				'db.user'     => config::get('local.user'),
				'db.password' => config::get('local.password'),
				'db.name'     => config::get('local.name')));

			// Attempt a connection
			db::connect();
		}

		// Define the constant SQL to whether there's an existing connection
		if(!defined('SQL')) define('SQL', db::connection(), FALSE);
	}

	/**
	 * Logs the current user's environnement
	 * @dependencies    config, mysql, constants
	 */
	function stats()
	{
		self::module('stats');

		// No active SQL connection or logs desactivated
		if(!config::get('logs') or !SQL)    return false;

		// The table doesn't exist
		if(!db::is_table('cerberus_logs'))
		{
			$update = update::table('cerberus_logs');
			if(!$update) return false;
		}

		// If we have a table we can start logging ----------------- /

		// Getting the user's IP
		$ip = server::ip();

		// Checking if it's already in the database or note
		if(db::field('cerberus_logs', 'ip', array('ip' => $ip)) and ($ip)) return false;

		// Getting informations
		$ua      = browser::detect();
		$domaine = url::domain();
		$mobile  = (browser::mobile() or browser::ios()) ? 1 : 0;

		// Saving informations
		if(!empty($ua['name']) and !empty($ua['platform']))
		{
			db::insert('cerberus_logs', array(
				'ip'       => $ip,
				'date'     => 'NOW()',
				'platform' => $ua['platform'],
				'browser'  => $ua['name'],
				'version'  => $ua['version'],
				'engine'   => $ua['engine'],
				'mobile'   => $mobile,
				'locale'   => l::locale(),
				'domaine'  => $domaine));
		}
	}

	/**
	* Creates a list of requried files and folders if they aren't there
	* @dependencies		dispatch, paths
	*/
	function required()
	{
		self::module('required');

		// List required files and their content
		$required = array(
			PATH_CACHE                                           => NULL,
			dispatch::path(PATH_CERBERUS.'{images}/{plugins}/')  => NULL,
			dispatch::path(PATH_CERBERUS.'{css}/{plugins}/')     => NULL,
			dispatch::path(PATH_CERBERUS.'{js}/{plugins}/')      => NULL);

		// Add path to custom Sass environnement
		if(LOCAL)
		{
			$required[dispatch::path(PATH_COMMON. '{sass}/_custom.sass')] = NULL;
			$required[dispatch::path(PATH_CERBERUS. '{sass}/base/_custom.sass')] = '@import ../../../../' .PATH_COMMON. 'sass/custom';
		}

		// Create files and folders
		foreach($required as $f => $content)
		{
			if(file_exists($f)) continue;
			if(substr($f, -1) == '/') dir::make($f);
			else f::write($f, $content);
		}
	}

	/**
	* Display debug informations
	* @dependencies		navigation, constants
	*/
	function debug()
	{
		self::module('debug');

		if(isset($_GET['cerberus_debug']))
		{
			// Get constants
			$constantes = get_defined_constants(true);
			$constantes = a::get($constantes, 'user');

			// Get main variables
			$debug	= "[<strong>URL</strong>] " .url::current().'<br/>'.PHP_EOL;
			if(self::loaded('navigation'))
			$debug .= "[<strong>PAGE</strong>] " .navigation::current().'<br/>'.PHP_EOL;
			$debug .= "[<strong>LANGUE</strong>] " .l::current().'<br/>'.PHP_EOL;

			// Display superglobals
			if($_GET)       $debug .= "[<strong>GET</strong>]\n\n<pre>" .print_r($_GET, true). '</pre>'.PHP_EOL;
			if($_POST)      $debug .= "[<strong>POST</strong>]\n\n<pre>" .print_r($_POST,true). '</pre>'.PHP_EOL;
			if($_SESSION)   $debug .= "[<strong>SESSION</strong>]\n\n<pre>" .print_r($_SESSION, true). '</pre>';
			if($constantes) $debug .= "[<strong>CONSTANTES</strong>]\n\n<pre>" .print_r($constantes, true). '</pre>';

			// Echo if local, hide in code if online
			echo LOCAL
				? '<div class="cerberus_debug">' .$debug. '</div>'
				: '<p style="display:none">' .str::unhtml($debug). '</p>';
		}
	}

	/**
	* Puts the current page in cache
	* @dependencies		navigation, constants
	*/
	function cache()
	{
		self::module('cache');

		if(CACHE)
		{
			// If the user hasn't already set some parameters
			if(!isset($setCache)) $setCache = array();

			// Set default parameters
			$setCache['name']                = a::get($setCache, 'name', navigation::current());
			$setCache['cache_time']          = a::get($setCache, 'cache_time');
			$setCache['cache_get_variables'] = a::get($setCache, 'cache_get_variables', true);
			$setCache['get_remove']          = a::get($setCache, 'get_remove', array('page', 'pageSub', 'PHPSESSID', 'langue', 'gclid', 'cerberus_debug'));
			$setCache['type']                = 'html';

			// Determine if we should cache the page or not
			// Exceptions are : page is administration ; page is set as not cached in the database ; in the $setCache
			// Default is true
			if(navigation::$page == 'admin') $caching = FALSE;
			elseif(SQL and db::is_table('cerberus_structure'))
				$caching = db::field(
					'cerberus_structure',
					'cache',
					db::where(array('CONCAT_WS("-",parent,page)' => $setCache['name'], 'parent' => $setCache['name']), 'OR'));

			if(!isset($caching)) $caching = a::get($setCache, 'cache', TRUE);

			// Start the output buffer
			if($caching) $start = cache::page($setCache['name'], $setCache);
		}
	}

	/**
	* Initialize the dispatch class
	*/
	function dispatch()
	{
		self::module('dispatch');
		new dispatch();
	}

	/**
	* Updates the Cerberus core
	*/
	function update()
	{
		self::module('update');
		new update();
	}

	/**
	* Backup the database
	* @dependencies		mysql,constants,cerberus
	*/
	function backup()
	{
		self::module('backup');
		if(db::connection() and CACHE and function_exists('backupSQL')) backupSQL();
	}

	function language()
	{
		self::module('language');

		l::cerberus();
	}

	function navigation()
	{
		self::module('navigation');
		new navigation();
	}
}
