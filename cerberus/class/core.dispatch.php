<?php
/**
 *
 * Dispatch
 *
 * This class handles assets, paths and
 * everything ressources-related
 *
 * @package Cerberus
 */
class dispatch
{
	/**
	 * The page we're currently in
	 * @var string
	 */
	static private $page;

	/**
	 *  The category we're currently in
	 * @var string
	 */
	static private $category;

	/**
	 * Whether Dispatch should try and add assets automatically or not
	 * Dispatch by default will add any asset matching the current page and base names (styles.css, scripts.js)
	 * @var boolean
	 */
	static private $guess = true;

	/* Current ressources ----------------------------------------- */

	/**
	 * Table containing the ressources to minify
	 * @var array
	 */
	static private $minify;

	/**
	 * Table containing the scripts and styles of the current page
	 * @var array
	 */
	static private $scripts;

	/**
	 * The current CSS ressources
	 * @var array
	 */
	static private $CSS;

	/**
	 * The current JS ressources
	 * @var array
	 */
	static private $JS;

	/**
	 * A Typekit ID if available
	 * @var string
	 */
	static private $typekit;

	/**
	 * Paths to every ressource available
	 * @var array
	 */
	static private $paths = array();

	/**
	 * A list of aliases for different scripts
	 * @var array
	 */
	static private $alias = array(
		// jQuery
		'jquery'      => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
		'jqueryui'    => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',

		// SWFObject
		'swfobject'   => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',

		// Plugins jQuery
		'nivoslider'  => array('nivo.slider', 'nivo-slider'),
		'uitotop'     => 'ui.totop');

	/**
	 * The file to cherry-pick in the linked submodules
	 * @var array
	 */
	static private $pluginFiles = array(
		'bootstrap'   => array(
			'css/bootstrap.css',
			'css/bootstrap-responsive.css',
			'img/*',
			'js/*'),
		'chosen'      => array(
			'chosen/chosen.css',
			'chosen/chosen.jquery.min.js',
			'chosen/chosen-sprite.png'),
		'colorbox'    => array(
			'colorbox/jquery.colorbox.js'),
		'font-awesome' => array(
			'css/font-awesome-ie7.css',
			'font/*'),
		'modernizr'   => array(
			'modernizr.js'),
		'nivoslider' => array(
			'nivo-slider.css',
			'jquery.nivo.slider.pack.js'),
		'noty'        => array(
			'css/jquery.noty.css',
			'css/noty_theme_twitter.css',
			'js/jquery.noty.js'),
		'tablesorter' => array(
			'js/jquery.tablesorter.min.js'),
			);

	//////////////////////////////////////////////////////////////////
	///////////////////////// PATHS AND FOLDERS //////////////////////
	//////////////////////////////////////////////////////////////////

	// Compass configuration file
	static public $compass  = 'config.rb';

	// Assets folder
	static public $assets   = 'assets';
	static public $cerberus = 'cerberus';
	static public $common   = 'common';
	static public $plugins  = 'plugins';

	// Filetypes folders
	static public $coffee   = 'coffee';
	static public $css      = 'css';
	static public $file     = 'file';
	static public $fonts    = 'fonts';
	static public $images   = 'img';
	static public $js       = 'js';
	static public $sass     = 'sass';
	static public $swf      = 'swf';

	//////////////////////////////////////////////////////////////////
	/////////////////////////// CONSTRUCT ////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Initializes the dispatch module
	 */
	public function __construct()
	{
		// Set up the main path constants
		self::paths();

		// Create Compass configuration file if unexisting
		if(LOCAL) self::compass();
	}

	/**
	 * Creates the basic layout of the assets array
	 */
	private static function structure()
	{
		self::$JS = self::$CSS =
			array(
			'min' => array(),
			'url' => array(),
			'inline' => array(
				'before' => array(),
				'after' => array()));
	}

	/**
	 * Search for, cache and create constants for the most common paths
	 */
	private static function paths()
	{
		// Set up the recurring paths
		$pathCommon   = config::get('path.common');
		$pathCerberus = config::get('path.cerberus');
		$pathFile     = config::get('path.file');
		$pathPlugins  = config::get('path.plugins');

		// If they're not cached in the config file, calculate them
		if(!$pathCommon or !$pathCerberus or !$pathFile or !$pathPlugins)
		{
			$pathCommon   = f::exist(
				self::path('{assets}/{common}/'),
				self::path('{assets}/'),
				'/');
			$pathCerberus = f::exist(
				self::path('{assets}/{cerberus}/'),
				self::path('{assets}/'),
				'/');
			$pathFile     = f::exist(
				self::path('{assets}/{common}/{file}/'),
				self::path('{assets}/{file}/'),
				self::path('{file}/'),
				'/');
			$pathPlugins  = self::path('{assets}/{plugins}/');

			// If we are in the root folder, cache into config file
			if(PATH_MAIN == null)
			{
				config::hardcode('path.common',   $pathCommon);
				config::hardcode('path.cerberus', $pathCerberus);
				config::hardcode('path.plugins',  $pathPlugins);
				config::hardcode('path.file',     $pathFile);
			}
		}

		// Define constans for easy access
		define('PATH_COMMON',   $pathCommon);
		define('PATH_CERBERUS', $pathCerberus);
		define('PATH_PLUGINS',  $pathPlugins);
		define('PATH_FILE',     $pathFile);
	}

	/**
	 * Fetch the current page and category from the navigation class
	 */
	private static function index()
	{
		if(!isset(self::$category))
		{
			self::$page     = (!empty($current)) ? $current : navigation::current();
			self::$category = navigation::$page;
		}
	}

	/**
	 * Change Dispatch's guess setting
	 * @param boolean $guess The new guess setting
	 */
	public static function setGuess($guess)
	{
		self::$guess = $guess;
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////// IMPORT ASSETS/MODULES ////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Sets the different PHP scripts for the different pages
	 *
	 * @param array  $modules The wanted modules
	 */
	public static function modules($modules)
	{
		self::index();

		if(!is_array($modules)) $modules = array('*' => func_get_args());
		$modules = self::unpack($modules);

		if($modules) foreach($modules as $m) self::addPHP($m);
	}

	/**
	 * Sets the different JS and CSS scripts for the different pages
	 *
	 * @param  array  $modules The wanted scripts and styles
	 * @return array  The scripts that were used in the current page
	 */
	public static function assets($scripts = array())
	{
		self::index();
		self::structure();

		// Emptying preexisting array
		if(!empty(self::$scripts))
			self::$scripts = array();

		/* Building the assets array ------------------------------ */

		// Forcing arrays where necessary
		if(!is_array($scripts)) $scripts = array('*' => func_get_args());
		$scripts['*']              = a::force_array($scripts['*']);
		$scripts[self::$page]      = a::force_array($scripts[self::$page]);
		$scripts[self::$category]  = a::force_array($scripts[self::$category]);

		// If Dispatch is authorized to guess assets to add, add default and current page
		if(self::$guess)
		{
			$scripts['*'][]              = 'scripts'; // default Javascript
			$scripts['*']               += array(99 => 'styles'); // default CSS
			$scripts[self::$page][]      = self::$page; // current page
			$scripts[self::$category][]  = self::$category; // current category

			// If Bootstrap, add it
			if(config::get('bootstrap'))
				$scripts['*'] = a::inject($scripts['*'], 0, 'bootstrap');
		}

		/* Creating the environnement ----------------------------- */

		// Getting assets paths
		self::listAssets();

		// Filtering the scripts array to keep the ones needed
		self::$scripts = self::unpack($scripts);

		// Loading the submodules files
		if(LOCAL) self::submodules(self::$scripts);

		/* Loading the wanted assets ------------------------------ */

		// If we have files to load
		if(self::$scripts)
		foreach(self::$scripts as $key => $value)
		{
			// Bootstrap Javascript files pack
			if($value == 'bootstrap-javascript')
				self::$JS['url'] = array_merge(self::$JS['url'], glob(PATH_CERBERUS.'js/bootstrap-*.js'));

			// Adding the available files to the CSS and JS arrays
			if(isset(self::$paths[$value]))
				foreach(self::$paths[$value] as $script)
				{
					// Ensure the asset is .css or .js
					$extension = strtoupper(f::extension($script));
					if($extension != 'CSS' and $extension != 'JS') continue;

					// If it's an external script, don't minify it
					if(str::find(array('http', 'bootstrap-'), $script)) self::${$extension}['url'][] = $script;
					else self::${$extension}['min'][] = $script;
				}
		}

		return self::$scripts;
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////////// HELPERS ////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Crawls through the assets folder and group them by a common alias
	 */
	private static function listAssets()
	{
		// Emptying the current list for refresh
		self::$paths = array();

		/* Listing available assets ------------------------------- */

		global $switcher;
		$templates = isset($switcher) ? 'assets/'.implode('/,asset/', $switcher->returnList()).'/' : null;

		// Creating a mask for authorized filepaths and file extensions
		$allowedFiles =
			'{' .PATH_COMMON. ',' .PATH_CERBERUS. ',' .$templates. '}' .
			'{' .self::$css. ',' .self::$js. '}' .
			'{/,/plugins/}*.{css,js}';
		$files = glob($allowedFiles, GLOB_BRACE);

		// Grouping each found ressource by name
		foreach($files as $path)
		{
			// Get base name
			$basename = f::name($path, true);

			// TODO : Better regex here, srsly
			if(!in_array($basename, array('jquery.min', 'admin')))
			{
				$basename = str::remove(array('min', 'pack', 'jquery'), $basename);
				$basename = trim($basename, '.-');
			}

			self::$paths[$basename][] = $path;
		}

		/* Renaming assets with aliases --------------------------- */

		// Getting preset aliases
		foreach(self::$alias as $to => $froms)
		{
			if(!is_array($froms)) $froms = array($froms);
			foreach($froms as $from)
			{
				// If the ressource we have an alias for is not in the folders
				if(!str::find('http', $from) and
					!a::get(self::$paths, $from.',0') and
					!isset(self::$paths[$from]))
						continue;

				// Case 1 : the old and new name both are in the array (merge)
				if(isset(self::$paths[$from], self::$paths[$to]))
				{
					$merge = array_merge(self::$paths[$to], self::$paths[$from]);

					// Reorder the array to have cerberus assets first when merged
					$reorder = array();
					foreach($merge as $m)
					{
						if(str::find('/cerberus/', $m)) array_unshift($reorder, $m);
						else $reorder[] = $m;
					}
					self::$paths[$to] = $reorder;
				}

				// Case 2 : The old name is in the array and need to be renamed to new (rename)
				elseif(isset(self::$paths[$from]) and !isset(self::$paths[$to])) self::$paths[$to] = self::$paths[$from];

				// Case 3 : The plugin is not in the array but needs to be because it's an external ressource (http)
				else self::$paths[$to][] = $from;

				// Removing the old entry from the array
				self::$paths = a::remove(self::$paths, $from);
			}
		}
	}

	/**
	 * Loads a given list of submodules into the Cerberus folder
	 *
	 * @param array  $submodules The list of required submodules
	 */
	public static function submodules($submodules)
	{
		// Getting an array or a list
		if(!is_array($submodules)) $submodules = func_get_args();

		// Compute the list of asked modules that exist
		$plugins = array_intersect($submodules, array_keys(self::$pluginFiles));

		// Gather the source files
		foreach($plugins as $plugin)
		{
			$pluginFiles = a::get(self::$pluginFiles, $plugin);
			// Check if the plugin is already loaded (file exists and is in plugin folder)
			if(isset(self::$paths[$plugin]))
				foreach(self::$paths[$plugin] as $check)
					if(str::find('/plugins/', $check) and in_array(basename($check), $pluginFiles)) break 2;

			// Check if we need the plugin
			if(!in_array($plugin, $submodules)) continue;

			// Check if the source files exist
			if(!file_exists(PATH_PLUGINS.$plugin.'/'))
			{
				errorHandle(
					'warning',
					'The source folder "' .PATH_PLUGINS.$plugin. '/" for plugin ' .$plugin. ' could not be found.');
				continue;
			}

			// Look for wildcards
			foreach($pluginFiles as $k => $v)
			{
				if(!str::find('*', $v)) continue;

				$glob = glob(PATH_PLUGINS.$plugin.'/'.$v, GLOB_BRACE);
				foreach($glob as $gk => $gv) $glob[$gk] = str::remove(PATH_PLUGINS.$plugin.'/', $gv);

				$pluginFiles = a::remove_value($pluginFiles, $v);
				$pluginFiles = array_merge($pluginFiles, $glob);
			}

			foreach($pluginFiles as $key => $value)
			{
				// Determine the folder to put the copied files into
				$type = f::type($value);

				// Determine the destination folder
				if($type == 'image') $extension = self::$images.'/plugins';
				elseif($type == 'fonts') $extension = self::$fonts;
				else $extension = f::extension($value).'/plugins';

				// Look for the paths
				$oldPath = PATH_PLUGINS.str::remove(PATH_PLUGINS, $plugin.'/'.$value);
				$newPath = PATH_CERBERUS.$extension.'/'.f::filename($value);

				// Ensuring the destination folder exists
				dir::make(f::dirname($newPath));

				// Copy the file or throw an error
				if(file_exists($oldPath) and $newPath) copy($oldPath, $newPath);
				else errorHandle('warning', 'The source file ' .$oldPath. ' could not be found.');

				$pluginFiles[$key] = $newPath;
			}
			self::$paths[$plugin] = $pluginFiles;
		}

		// Crawl the folders again to add the new files
		self::listAssets();
	}

	/**
	 * Sort and filters an array of asked modules
	 *
	 * @param  array  $modules An array containing the wanted modules globally
	 * @return array  Only the needed modules in all those given
	 */
	private static function unpack($modules)
	{
		// Looking for groups (group1,group2)
		foreach($modules as $key => $value)
		{
			$value = a::force_array($value);
			if(str::find(',', $key))
			{
				// Separation of the groups
				$keys = explode(',', $key);
				foreach($keys as $pages)
				{
					if(!is_array($value)) $value = array($value);
					$modules[$pages] = (isset($modules[$pages]))
						? array_merge($value, a::force_array($modules[$pages]))
						: $value;
				}

				// Removing the group remains from the main array
				$modules = a::remove($modules, $key);
			}
		}

		// Getting the scripts that are concerned by the current page
		$modules['*']             = a::force_array($modules['*']);
		$modules[self::$category] = a::force_array($modules[self::$category]);
		$modules[self::$page]     = a::force_array($modules[self::$page]);

		// Filter the arrays
		$assets = array_merge($modules['*'], $modules[self::$category], $modules[self::$page]);
		$assets = a::clean($assets);

		// Looking for a !script flag to remove a particular script
		foreach($assets as $key => $value)
		{
			if(str::find('!', $value))
			{
				$assets = a::remove_value($assets, substr($value, 1));
				$assets = a::remove($assets, $key);
			}
		}

		// Return filtered array
		return !empty($assets) ? $assets : false;
	}

	/**
	 * Cleans an array of asset from duplicates and empty strings
	 *
	 * @param  array  $array The array to sanitize
	 * @return array  The sanitized array
	 */
	private static function sanitize($array)
	{
		// Creating link to Minify
		if(isset($array['min']) and !empty($array['min']))
		{
			$minify = a::clean($array['min']);
			if(!is_dir('min') or !config::get('minify') or !$minify)
				$array['url'] = array_merge($array['url'], $minify);
			else
				$array['url'][] = 'min/?f=' .implode(',', $minify);
		}

		// Filtering array
		if($array['url'])
			$array['url'] = a::clean($array['url']);

		return $array;
	}

	/**
	 * Takes a list of singled-out scripts and wrap them in a javascript block
	 *
	 * @param  array   $scripts An array of Javascript bits
	 * @return string  A <script> block
	 */
	private static function inlineJS($scripts)
	{
		$inline  = '<script>';
		$inline .= PHP_EOL.implode("\n", $scripts).PHP_EOL;
		$inline .= '</script>';
		return $inline;
	}

	//////////////////////////////////////////////////////////////////
	//////////////////////////// UTILITIES ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Returns a given path, replacing all keys (ex: {assets}) by their configured path
	 * Example : {assets}/{common}/{css}/ will return assets/my_theme/stylesheets
	 *
	 * @param  string  $path The path to format
	 * @return string  The formatted path
	 */
	public static function path($path)
	{
		preg_match_all('#\{([a-z]+)\}#', $path, $results);

		// If we found aliases, replace them
		if($results) foreach($results[0] as $id => $r)
		{
			$variable = a::get($results[1], $id);
			$variable = self::${$variable};

			$path = str_replace($r, $variable, $path);
		}
		return $path;
	}

	/**
	 * Returns the current stylesheets
	 *
	 * @return array  An array containing all stylesheets on the page
	 */
	public static function currentCSS()
	{
		return self::$CSS['url'];
	}

	/**
	 * Returns the current javascript files
	 *
	 * @return array  An array containing all scripts on the page
	 */
	public static function currentJS()
	{
		return self::$JS['url'];
	}

	/**
	 * Tells if a given script is used on the current page or not
	 *
	 * @param  string   The name of a script
	 * @return boolean  Whether or not the script is used on this page
	 */
	public static function isScript($script)
	{
		return (isset(self::$scripts) and in_array($script, self::$scripts));
	}

	//////////////////////////////////////////////////////////////////
	//////////////////////// CSS & JAVASCRIPT ////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Injects scripts or styles in the page
	 *
	 * @param string  $type    The type of the injected asset, css or js
	 * @param array   $scripts Either an array of assets or a single asset
	 * @param array   $params  Parameters to pass to the current given scripts
	 *                         -- place[before/after] : Place before/after including stylesheets/scripts (default after)
	 *                         -- alias               : An alias to give the given script
	 *                         -- wrap[window/ready]  : Wraps the script with (window).load or (document).ready
	 */
	private static function inject($type, $scripts, $params = array())
	{
		if(!is_array($scripts)) $scripts = array($scripts);
		if(!is_array($params))  json_decode($params);

		// Check the paths and assets are initialized
		if(empty(self::$paths))
			self::assets();

		// Default parameters (no alias/after/no wrap)
		$type  = str::upper($type);
		$alias = a::get($params, 'alias');
		$place = $type == 'css' ? 'after' : a::get($params, 'place', 'after');
		$wrap  = a::get($params, 'wrap');

		// Reading the given scripts
		foreach($scripts as $script)
		{
			// If it's a script we know, match it to its path(s)
			if(isset(self::$paths[$script]))
			{
				foreach(self::$paths[$script] as $s)
				{
					$extension = strtoupper(f::extension($s));
					self::${$extension}['min'][] = $s;
				}
				continue;
			}

			// Clean up the script from any wrapping tag
			$script = preg_replace('#(<script>|</script>|<style>|</style>)#', null, $script);

			// If it's an external link or not
			$isHttp = str::find('http', substr($script, 0, 4));

			// If we added a link to another file
			if(in_array(f::extension($script), array('css', 'js')) or $isHttp)
			{
				if($isHttp) self::${$type}['url'][] = $script;
				else self::${$type}['min'][] = $script;
			}

			// If we added pure code to put into tags
			else
			{
				$script = trim($script);
				if($alias) self::${$type}['inline'][$place][$alias] = $script;
				else self::${$type}['inline'][$place][] = $script;
			}
		}
	}

	/**
	 * Fetch the current CSS styles for the page
	 */
	public static function getCSS($return = false)
	{
		if(a::array_empty(self::$CSS) and a::array_empty(self::$JS)) self::assets();
		self::$CSS = self::sanitize(self::$CSS);
		$head = array();

		if(self::$CSS['url']) foreach(self::$CSS['url'] as $url) head::stylesheet($url);
		if(self::$CSS['inline']['after']) head::css("\n\t\t".implode("\n\t\t", self::$CSS['inline']['after'])."\n\t");

		$head = "\t".implode(PHP_EOL."\t", $head).PHP_EOL;
		if($return) return $head;
		else echo $head;
	}

	/**
	 * Fetch the current JS scripts for the page
	 */
	public static function getJS($return = false)
	{
		if(isset(self::$typekit)) self::addJS('http://use.typekit.com/' .self::$typekit. '.js');
		self::$JS = self::sanitize(self::$JS);
		$head = array();

		if(self::$JS['inline']['before']) $head[] = self::inlineJS(self::$JS['inline']['before']);
		if(self::$JS['url']) foreach(self::$JS['url'] as $url) $head[] = '<script src="' .$url. '"></script>';
		if(self::$JS['inline']['after']) $head[] = self::inlineJS(self::$JS['inline']['after']);

		$head = "\t".implode(PHP_EOL."\t", $head).PHP_EOL;
		if($return) return $head;
		else echo $head;
	}

	//////////////////////////////////////////////////////////////////
	////////////////////////////// SHORTCUTS /////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Add a stylesheet/style to the current page
	 */
	public static function addCSS($stylesheets)
	{
		self::inject('css', $stylesheets);
	}

	/**
	 * Remove a stylesheet from a page
	 */
	public static function removeCSS($stylesheets)
	{
		if(in_array($stylesheets, self::$CSS['min'])) self::$CSS['min'] = a::remove(self::$CSS['min'], $stylesheets, false);
		if(in_array($stylesheets, self::$CSS['url'])) self::$CSS['url'] = a::remove(self::$CSS['url'], $stylesheets, false);
	}

	/**
	 * Add Javascript before the links
	 */
	public static function addJSBefore($javascript, $params = null)
	{
		$params = array_merge($params, array('place' => 'before'));
		self::inject('js', $javascript, $params);
	}

	/**
	 * Add Javascript after the links
	 */
	public static function addJS($javascript, $params = array())
	{
		self::inject('js', $javascript, $params);
	}

	/**
	 * Include a PHP script
	 */
	public static function addPHP($module)
	{
		$availableFiles =
			'{' .PATH_CORE. '{tools,class,class/plugins},' .PATH_COMMON. 'php}/' .
			'{' .$module. ',class.' .$module. '}.php';
		$file = glob($availableFiles, GLOB_BRACE);

		if($file)
			if(!function_exists($module) and !class_exists($module))
				include(a::get($file, 0));
	}

	//////////////////////////////////////////////////////////////////
	///////////////////////// MODULES AND API ////////////////////////
	//////////////////////////////////////////////////////////////////

	/* SASS & COMPASS --------------------------------------------- */

	/**
	 * Setup a Compass configuration file
	 *
	 * @param  array  $config An array containing configuration parameters
	 */
	public static function compass($config = array())
	{
		$file = null;

		// If we don't already have a configuration file
		if(!file_exists(PATH_CERBERUS.self::$compass) or !file_exists(PATH_MAIN.self::$compass))
		{
			// Fetch default configuration options
			$configuration = array(
				'0'                => 'Folders',
				'project_path'     => substr(PATH_COMMON, 0, -1),
				'images_dir'       => self::$images,
				'css_dir'          => self::$css,
				'javascripts_dir'  => self::$js,
				'fonts_dir'        => self::$fonts,

				'1'                => 'Options',
				'output_style'     => ':expanded',
				'preferred_syntax' => ':sass',
				'line_comments'    => 'false',
				'relative_assets'  => 'true',

				'2'                => 'Extensions');

			// Merge with given configuration parameters
			$configuration = array_merge($config, $configuration);
			$extensions    = config::get('compass');

			// Writing options
			foreach($configuration as $k => $v)
			{
				// If value is comment
				if(is_numeric($k))
				{
					if(!empty($file)) $file .= PHP_EOL;
					$file .= '# ' .$v.PHP_EOL;
					continue;
				}

				// If value is array
				elseif(is_array($v)) $v = json_encode($v);

				// If value is boolean
				elseif(!($v == 'true' or $v == 'false' or (substr($v, 0, 1) == ':'))) $v = '"' .$v. '"';

				$file .= $k. ' = ' .$v.PHP_EOL;
			}

			// Loading extensions
			foreach($extensions as $e)
				$file .= "require '" .$e. "'".PHP_EOL;

			// Write core configuration file in root
			f::write(self::$compass, $file);

			// Go through the different template and create a Compass config in each one
			$folders = glob('assets/*/');
			foreach($folders as $f)
				if(!in_array(basename($f), array('plugins', 'common'))) f::write($f.self::$compass, $file);
		}
	}

	/* JAVASCRIPT ------------------------------------------------- */

	/**
	 * Add a basic jQuery plugin
	 *
	 * @param  string $plugin   The plugin name
	 * @param  string $selector The selector to apply the plugin to (can be null)
	 * @param  array  $params   An array of parameters to pass to the plugin
	 */
	public static function plugin($plugin, $selector = null, $params = null)
	{
		// Getting parameters
		$params = func_get_args();
		$paramString = array();
		foreach($params as $k => $p)
		{
			if($k < 2) continue;
			if(is_array($p)) $p = json_encode($p);
			$paramString[] = $p;
		}
		$string = $plugin. '(' .implode(', ', $paramString). ')';

		// Getting selector
		if($selector !== null)
		{
			$selector = empty($selector) ? '$' : "$('" .addslashes($selector). "')";
			$string = $selector.'.'.$string;
		}

		// Adding the JS bit
		self::addJS($string.';');
	}

	/**
	 * Add a Google Analytics account
	 *
	 * @param  string $analytics A Google Analytics ID
	 */
	public static function analytics($analytics = 'XXXXX-X')
	{
		self::addJS("var _gaq=[['_setAccount','UA-" .$analytics. "'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'));");
	}

	/* WEBFONTS --------------------------------------------------- */

	/**
	 * Add a Typekit kit
	 *
	 * @param  string $kit The Typekit ID
	 */
	public static function typekit($kit = 'xky6uxx')
	{
		self::$typekit = $kit;
		self::addJS('try{Typekit.load();}catch(e){};');
	}

	/**
	 * Embbed Google webfonts on the page
	 *
	 * @param  string Several fonts to include
	 */
	public static function googleFonts()
	{
		$fonts = func_get_args();

		// Fetching the fonts, converting to GF syntax
		$fonts = implode('|', $fonts);
		$fonts = str_replace(' ' , '+', $fonts);
		$fonts = str_replace('*' , '100,200,300,400,500,600,700,800,900', $fonts);

		self::addCSS('http://fonts.googleapis.com/css?family=' .$fonts);
	}
}
