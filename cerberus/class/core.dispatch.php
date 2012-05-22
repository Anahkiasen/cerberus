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
	// The page we're currently in
	static private $current;
	
	// The category we're currently in
	static private $global;
	
	/* Current ressources ------------------------------------- */
	
	// Table containing the ressources to minify
	static private $minify;
	
	// Table containing the scripts and styles of the current page
	static private $scripts;

	// The current CSS ressources
	static private $CSS;
	
	// The current JS ressources
	static private $JS;
		
	// A Typekit ID if available
	static private $typekit;
	
	// Paths to every ressource available
	static private $paths = array();
	
	// A list of aliases for different scripts
	static private $alias = array(
		// jQuery
		'jquery'      => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',		
		'jqueryui'    => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',

		// SWFObject
		'swfobject'   => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		
		// Plugins jQuery
		'tablesorter' => 'jquery.tablesorter.min',
		'nivoslider'  => 'jquery.nivo.slider.pack',
		'colorbox'    => 'jquery.colorbox-min',
		'chosen'      => 'chosen.jquery.min',
		'easing'      => 'jquery.easing',
		'noty'        => 'jquery.noty');
		
	// The file to cherry-pick in the linked submodules
	static private $plugins_files = array(
		'bootstrap'   => array(
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
			'sass/font-awesome.sass',
			'font/*'),
		'modernizr'   => array(
			'modernizr.js'),
		'nivoslider' => array(
			'jquery.nivo.slider.pack.js'),
		'noty'        => array(
			'css/jquery.noty.css',
			'css/noty_theme_twitter.css',
			'js/jquery.noty.js'),
		'tablesorter' => array(
			'js/jquery.tablesorter.min.js'),
			);
		
	//////////////////////////////////////////////////////////////
	////////////////////// PATHS AND FOLDERS ///////////////////// 
	//////////////////////////////////////////////////////////////
	
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
	
	//////////////////////////////////////////////////////////////
	//////////////////////////// CONSTRUCT /////////////////////// 
	//////////////////////////////////////////////////////////////	
	
	/**
	 * Initializes the dispatch module
	 * 
	 * @param string	$current	The current batch identifier, if not specified, uses the current page
	 */
	function __construct($current = NULL)
	{			
		// Basic layour of the assets arrays
		self::$JS = self::$CSS =
			array(
			'min' => array(),
			'url' => array(),
			'inline' => array(
				'before' => array(),
				'after' => array()));
				
		// Set up the recurring paths
		$path_common   = config::get('path.common');
		$path_cerberus = config::get('path.cerberus');
		$path_file     = config::get('path.file');
		$path_plugins  = config::get('path.plugins');
		
		// If they're not cached in the config file, calculate them
		if(!$path_common or !file_exists($path_common))
		{
			$path_common   = f::path(
				self::path('{assets}/{common}/'),
				self::path('{assets}/'),
				NULL);
			$path_cerberus = f::path(
				self::path('{assets}/{cerberus}/'),
				self::path('{assets}/'),
				NULL);
			$path_file     = f::path(
				self::path('{assets}/{common}/{file}/'),
				self::path('{assets}/{file}/'),
				self::path('{file}/'));
			$path_plugins  = f::path(
				self::path('{assets}/{plugins}/'));
			
			// Cache into config file
			if(PATH_MAIN == NULL)
			{
				config::hardcode('path.common',   $path_common);
				config::hardcode('path.cerberus', $path_cerberus);
				config::hardcode('path.plugins',  $path_plugins);
				config::hardcode('path.file',     $path_file);
			}
		}
		
		// Define constans for easy access
		define('PATH_COMMON',   $path_common);
		define('PATH_CERBERUS', $path_cerberus);
		define('PATH_PLUGINS',  $path_plugins);
		define('PATH_FILE',     $path_file);
		
		// Create Compass configuration file if unexisting
		if(LOCAL) self::compass();
	}
	
	/**
	 * Fetch the current page and category from the navigation class
	 */
	private static function index()
	{
		if(!isset(self::$global))
		{
			self::$current = (!empty($current)) ? $current : navigation::current();
			self::$global = navigation::$page;
		}
	}	
	
	//////////////////////////////////////////////////////////////
	///////////////////// ASSETS CONFIGURATION /////////////////// 
	//////////////////////////////////////////////////////////////

	/**
	 * Sets the different PHP scripts for the different pages
	 * 
	 * @param array 	$modules The wanted modules
	 */
	static function setPHP($modules)
	{
		self::index();
		
		if(!is_array($modules)) $modules = array('*' => func_get_args());
		$modules = self::unpack($modules);
		if($modules) new Cerberus($modules, self::$global);
	}
	
	/**
	 * Sets the different JS and CSS scripts for the different pages
	 * 
	 * @param array 	$modules The wanted scripts and styles
	 * @return array  The scripts that were used in the current page
	 */
	static function assets($scripts = array())
	{
		global $switcher;
		self::index();
		
		##################
		# INITIALIZATION #
		##################
		
		// Mise en array des différents scripts
		if(!is_array($scripts)) $scripts = array('*' => func_get_args());
		$scripts['*'] = a::force_array($scripts['*']);
		$scripts[self::$current] = a::force_array($scripts[self::$current]);
		$scripts[self::$global] = a::force_array($scripts[self::$global]);
		
		// Fichiers par défaut
		$scripts['*'][] = 'core';
		$scripts['*'] += array(99 => 'styles');	
		$scripts[self::$current][] = self::$current;
		$scripts[self::$global][] = self::$global;
		
		// Modules intégrés
		if(config::get('bootstrap'))
		{	
			$scripts['*'] = a::inject($scripts['*'], 0, 'bootstrap');
			$bootstrap_modules = glob(PATH_CERBERUS.'js/bootstrap-*.js');
		}
		if(config::get('modernizr')) $scripts['*'] = a::inject($scripts['*'], 0, 'modernizr');
		
		############################
		# GETTING AVAILABLE ASSETS #
		############################
				
		$templates = isset($switcher) ? ','.implode(',', $switcher->returnList()) : NULL;
		$allowed_files = '{' .self::$css. '{/,/plugins/}*.css,' .self::$js. '{/,/plugins/}*.js}';
		$files = glob('{' .PATH_COMMON. ',' .PATH_CERBERUS. '}' .$allowed_files, GLOB_BRACE);
		
		// Creating the path list
		foreach($files as $path)
		{
			$basename = f::name($path, true);
			self::$paths[$basename][] = $path;
		}
		
		// Getting preset aliases
		foreach(self::$alias as $s => $paths)
		{
			if(!is_array($paths)) $paths = array($paths);
			foreach($paths as $p)
			{
				if(!str::find('http', $p) and !file_exists($p) and !file_exists(a::get(self::$paths, $p.',0'))) continue;
				
				if(isset(self::$paths[$p], self::$paths[$s])) self::$paths[$s] = array_merge(self::$paths[$s], self::$paths[$p]);
				elseif(isset(self::$paths[$p]) and !isset(self::$paths[$s])) self::$paths[$s] = self::$paths[$p];
				else self::$paths[$s][] = $p;
				self::$paths = a::remove(self::$paths, $p);
			}
		}
		
		##########################
		# LOADING CURRENT ASSETS #
		##########################
		
		// Unpacking the wanted scripts
		self::$scripts = self::unpack($scripts);
		
		// Loading the submodules files
		self::submodules(self::$scripts);
		
		if(self::$scripts) foreach(self::$scripts as $key => $value)
		{
			// Bootstrap
			if($value == 'bsjs')
				self::$JS['url'] = array_merge(self::$JS['url'], $bootstrap_modules);
			
			if(isset(self::$paths[$value]))
				foreach(self::$paths[$value] as $script)
				{
					$extension = strtoupper(f::extension($script));
					if($extension != 'CSS' and $extension != 'JS') continue;
					if(str::find(array('http', self::$js.'/bootstrap-'), $script)) self::${$extension}['url'][] = $script;
					else self::${$extension}['min'][] = $script;
				}
		}
		
		return self::$scripts;
	}

	/**
	 * Loads a given list of submodules into the Cerberus folder
	 * 
	 * @param array    $submodules The list of required submodules
	 */
	private static function submodules($submodules)
	{
		// Gather the source files
		foreach(self::$plugins_files as $plugin => $plugin_files)
		{	
			// Check if the plugin is already loaded
			if(isset(self::$paths[$plugin])) continue;
			
			// Check if the plugin was required
			if(!in_array($plugin, $submodules)) continue;
			
			// Check if the source files exist
			if(!file_exists(PATH_PLUGINS.$plugin.'/'))
			{
				echo 'The source folder for plugin ' .$plugin. ' was not found';
				continue;
			}	
			
			// Look for wildcards
			foreach($plugin_files as $k => $v)
			{
				if(!str::find('*', $v)) continue;
				
				$glob = glob(PATH_PLUGINS.$plugin.'/'.$v, GLOB_BRACE);
				foreach($glob as $gk => $gv) $glob[$gk] = str::remove(PATH_PLUGINS.$plugin.'/', $gv);
				
				$plugin_files = a::remove_value($plugin_files, $v);
				$plugin_files = array_merge($plugin_files, $glob);
			}
			
			foreach($plugin_files as $key => $value)
			{
				// Determine the folder to put the copied files into
				$type = f::type($value);
				
				// Determine the destination folder
				if($type == 'image') $extension = self::$images.'/plugins';
				elseif($type == 'fonts') $extension = self::$fonts;
				else $extension = f::extension($value).'/plugins';
				
				// Look for the paths
				$old_path = PATH_PLUGINS.str::remove(PATH_PLUGINS, $plugin.'/'.$value);
				$new_path = PATH_CERBERUS.$extension.'/'.f::filename($value);
				
				// Ensuring the destination folder exists
				dir::make(f::dirname($new_path));
				
				// Copy the file or throw an error
				if(file_exists($old_path) and $new_path) copy($old_path, $new_path);
				else echo 'The source file ' .$old_path. ' could not be found.'.PHP_EOL;
				
				$plugin_files[$key] = $new_path;
			}
			self::$paths[$plugin] = $plugin_files;
		}
	}

	/**
	 * Sort and filters an array of asked modules
	 * 
	 * @param array 	$modules An array containing the wanted modules globally
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
		$modules['*'] = a::force_array($modules['*']);
		$modules[self::$global] = a::force_array($modules[self::$global]);
		$modules[self::$current] = a::force_array($modules[self::$current]);
		
		// Filter the arrays
		$assets = array_merge($modules['*'], $modules[self::$global], $modules[self::$current]);
		$assets = array_unique(array_filter($assets));
		
		// Looking for a !script flag to remove a particular script
		foreach($assets as $key => $value)
		{
			if(str::find('!', $value))
			{
				$assets = a::remove_value($assets,  substr($value, 1));
				$assets = a::remove($assets, $key);
			}
		}
		
		// Return filtered array
		return !empty($assets) ? $assets : FALSE;
	}
	
	//////////////////////////////////////////////////////////////
	//////////////////////// UTILITIES /////////////////////////// 
	//////////////////////////////////////////////////////////////
	
	/**
	 * Returns a given path, replacing all keys (ex: {assets}) by their configured path
	 * Example : {assets}/{common}/{css}/ => assets/my_theme/stylesheets
	 * 
	 * @param  string   $path The path to format
	 * @return string   The formatted path
	 */
	static function path($path)
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
	 */ 
	static function currentCSS()
	{
		return self::$CSS['url'];
	}
	
	/**
	 * Returns the current javascript files
	 */ 
	static function currentJS()
	{
		return self::$JS['url'];
	}
	
	/**
	 * Tells if a given script is used on the current page or not
	 * 
	 * @param  string   The name of a script
	 * @return boolean  Whether or not the script is used on this page
	 */
	static function isScript($script)
	{
		return (isset(self::$scripts) and in_array($script, self::$scripts));
	}
	
	/* 
	########################################
	########## CSS ET JAVASCRIPT ###########
	########################################
	*/
	
	/**
	 * Injects scripts or styles in the page
	 * 
	 * @param string 	$type The type of the injected asset, css or js
	 * @param array   $scripts Either an array of assets or a single asset
	 * @param array   $params Parameters to pass to the current given scripts
	 *                -- place[before/after] : Place the script before or after including stylesheets/scripts (default after)
	 *                -- alias : An alias to give the given script
	 *                -- wrap[window/ready] : Wraps the script with (window).load or (document).ready
	 */
	static function inject($type, $scripts, $params = array())
	{
		if(!is_array($scripts)) $scripts = array($scripts);
		if(!is_array($params)) json_decode($params);
		
		// Paramètres par défaut du script inclus
		$type  = str::upper($type);
		$alias = a::get($params, 'alias');
		$place = $type == 'css' ? 'after' : a::get($params, 'place', 'after');
		$wrap  = a::get($params, 'wrap');
		
		// Lecture des scripts fournis
		foreach($scripts as $script)
		{
			// Si l'on demande un script connu de Cerberus
			if(isset(self::$paths[$script]))
				foreach(self::$paths[$script] as $s)
				{
					$extension = strtoupper(f::extension($s));
					self::${$extension}['min'][] = $s;
				}
			
			// Si l'on demande une URL vers un script externe
			$script = preg_replace('#(<script>|</script>|<style>|</style>)#', NULL, $script);
			$is_http = str::find('http', substr($script, 0, 4));
			if(in_array(f::extension($script), array('css', 'js')) or $is_http)
			{
				if($is_http) self::${$type}['url'][] = $script;
				else self::${$type}['min'][] = $script;
			}
			
			// Si l'on demande l'ajout d'un script à la page
			else
			{
				$script = trim($script);
				
				if($alias) self::${$type}['inline'][$place][$alias] = trim($script);
				else self::${$type}['inline'][$place][] = trim($script);
			}	
		}
	}
	
	
	/**
	 * Fetch the current CSS styles for the page
	 */
	static function getCSS($return = false)
	{
		if(a::array_empty(self::$CSS)) self::assets();
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
	static function getJS($return = false)
	{
		if(isset(self::$typekit)) self::addJS('http://use.typekit.com/' .self::$typekit. '.js');
		self::$JS = self::sanitize(self::$JS);
		$head = array();
		
		if(self::$JS['inline']['before']) $head[] = self::inline_js(self::$JS['inline']['before']);
		if(self::$JS['url']) foreach(self::$JS['url'] as $url) $head[] = '<script src="' .$url. '"></script>';
		if(self::$JS['inline']['after']) $head[] = self::inline_js(self::$JS['inline']['after']);
		
		$head = "\t".implode(PHP_EOL."\t", $head).PHP_EOL;
		if($return) return $head;
		else echo $head;
	}
	
	/**
	 * Cleans an array of asset from duplicates and empty strings
	 * 
	 * @param  array 	$array The array to sanitize
	 * @return array 	The sanitized array
	 */
	private static function sanitize($array)
	{
		if(isset($array['min']) and !empty($array['min']))
		{
			$minify = array_unique(array_filter($array['min']));
			if(!is_dir('min') or !config::get('minify') or !$minify)
				$array['url'] = array_merge($array['url'], $minify);
			else
				$array['url'][] = 'min/?f=' .implode(',', $minify). '';	
		}
		$array['url'] = array_unique(array_filter($array['url']));
		
		return $array;
	}
	
	private static function inline_js($scripts)
	{
		content::start(); ?>
		<script type="text/javascript">
			<?= PHP_EOL.implode("\n", $scripts).PHP_EOL ?>
		</script>
		<?
		return content::end(true);
	}
	
	/* Raccourcis ---------------------------------------------- */
	
	/* Ajoute une feuille de style */
	static function addCSS($stylesheets)
	{
		self::inject('css', $stylesheets);
	}
	
	/* Enlève une feuille de style */
	static function removeCSS($stylesheets)
	{
		if(in_array($stylesheets, self::$CSS['min'])) self::$CSS['min'] = a::remove(self::$CSS['min'], $stylesheets, false);
		if(in_array($stylesheets, self::$CSS['url'])) self::$CSS['url'] = a::remove(self::$CSS['url'], $stylesheets, false);
	}
	
	/* Ajout de scripts à la volée */
	static function addJSBefore($javascript, $params = NULL)
	{
		$params = array_merge($params, array('place' => 'before'));
		self::inject('js', $javascript, $params);
	}
	
	static function addJS($javascript, $params = array())
	{
		self::inject('js', $javascript, $params);
	}
	
	//////////////////////////////////////////////////////////////
	////////////////////// MODULES AND API /////////////////////// 
	//////////////////////////////////////////////////////////////
	
	/* ---------- SASS & COMPASS ---------- */
	
	// Setup a Compass configuration file
	static function compass($config = array())
	{
		$file = NULL;
		
		// If we don't already have a configuration file
		if(!file_exists(PATH_CERBERUS.self::$compass) or !file_exists(self::$compass))
		{
			// Fetch default configuration options
			$configuration = array(
				'images_dir'       => self::$images,
				'css_dir'          => self::$css,
				'javascripts_dir'  => self::$js,
				'fonts_dir'        => self::$fonts,
				'output_style'     => ':expanded',
				'preferred_syntax' => ':sass',
				'line_comments'    => 'false',
				'relative_assets'  => 'true');
				
			// Merge with given configuration parameters
			$configuration = array_merge($config, $configuration);
			$extensions = config::get('compass');
			
			// Writing options
			foreach($configuration as $k => $v)
			{
				if(is_array($v)) $v = json_encode($v);
				elseif(!($v == 'true' or $v == 'false' or (substr($v, 0, 1) == ':'))) $v = '"' .$v. '"';
				$file .= $k. ' = ' .$v.PHP_EOL;
			}

			// Loading extensions
			$file .= PHP_EOL.'# Extensions'.PHP_EOL;
			foreach($extensions as $e) $file .= "require '" .$e. "'".PHP_EOL;
			
			// Writing the configuration files
			f::write(PATH_CERBERUS.self::$compass, $file);
			f::write(self::$compass, 'project_path = "' .substr(PATH_COMMON, 0, -1). '"'.PHP_EOL.$file);	
		}	
	}

	/* ---------- JAVASCRIPT ---------- */
	
	// Add a basic jQuery plugin
	static function plugin($plugin, $selector = NULL, $params = NULL)
	{
		// Getting parameters
		if(is_array($params)) $params = json_encode($params);
		$string = $plugin. '(' .$params. ')';
		
		// Getting selector
		if($selector !== NULL)
		{
			$selector = empty($selector) ? '$' : "$('" .$selector. "')";
			$string = $selector.'.'.$string.';';
		}
		
		// Adding the JS bit
		self::addJS($string);
	}
	
	// Add a Google Analytics account
	static function analytics($analytics = 'XXXXX-X')
	{
		self::addJS("var _gaq=[['_setAccount','UA-" .$analytics. "'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))");
	}
	
	/* ---------- WEBFONTS ---------- */
	
	// Typekit
	static function typekit($kit = 'xky6uxx')
	{
		self::$typekit = $kit;
		self::addJS('try{Typekit.load();}catch(e){}');
	}
	
	// Google Webfonts
	static function googleFonts()
	{
		$fonts = func_get_args();
		
		// Fetching the fonts, converting to GF syntax
		$fonts = implode('|', $fonts);
		$fonts = str_replace(' ' , '+', $fonts);
		$fonts = str_replace('*' , '100,200,300,400,500,600,700,800,900', $fonts);
		
		self::addCSS('http://fonts.googleapis.com/css?family=' .$fonts);
	}
}
?>