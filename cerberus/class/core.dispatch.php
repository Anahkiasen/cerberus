<?php
class dispatch extends Cerberus
{
	static private $current;
	static private $global;
	static private $minify;
	static private $scripts;

	/* Tableaux des ressources	 */
	static private $CSS;
	static private $JS;
		
	/* API disponibles */
	static private $typekit;
	static private $paths = array();
	static private $availableAPI = array(
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.18/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js',
		'tablesorter' => 'jquery.tablesorter.min',
		'nivoslider' => 'jquery.nivo.slider.pack',
		'chosen' => 'chosen.jquery.min',
		'colorbox' => 'jquery.colorbox-min',
		'easing' => 'jquery.easing',
		'noty' => 'jquery.noty');
		
	/***************************
	 ** RESSOURCES ET CHEMINS **
	 ***************************/
	
	static public $compass = 'config.rb';
	
	static public $assets = 'assets';
	static public $cerberus = 'cerberus';
	static public $common = 'common';
	
	static public $fonts = 'fonts';
	static public $images = 'img';
	static public $css = 'css';
	static public $sass = 'sass';
	static public $js = 'js';
	static public $coffee = 'coffee';
	static public $file = 'file';
	
	/**
	 * Initializes the dispatch module
	 * 
	 * @param string	$current	The current batch identifier, if not specified, uses the current page
	 */
	function __construct($current = NULL)
	{			
		// Paramètres
		self::$JS = self::$CSS =
			array(
			'min' => array(),
			'url' => array(),
			'inline' => array(
				'before' => array(),
				'after' => array()));
				
		// Chemins récurrents
		$path_common =    config::get('path.common');
		$path_cerberus =  config::get('path.cerberus');
		$path_file =      config::get('path.file');
		$path_cache =     'cerberus/cache/';
		
		// Chemins par défaut
		if(!$path_common or !file_exists($path_common))
		{
			$path_common =    f::path(dispatch::path('{assets}/{common}/'),        f::path(dispatch::path('{assets}/'), ''));
			$path_cerberus =  f::path(dispatch::path('{assets}/{cerberus}/'),      f::path(dispatch::path('{assets}/'), ''));
			$path_file =      f::path(dispatch::path('{assets}/{common}/{file}/'), f::path(dispatch::path('{assets}/{file}/'), f::path(dispatch::path('{file}/'))));
			
			if(PATH_MAIN == NULL)
			{
				config::hardcode('path.common',   $path_common);
				config::hardcode('path.cerberus', $path_cerberus);
				config::hardcode('path.file',     $path_file);
			}
		}
		
		define('PATH_COMMON',   $path_common);
		define('PATH_CERBERUS', $path_cerberus);
		define('PATH_FILE',     $path_file);
		define('PATH_CACHE',    $path_cache);
		
		if(LOCAL) self::compass();
	}
	
	// Configure les indexs
	static function index()
	{
		if(!isset(self::$global))
		{
			self::$current = (!empty($current)) ? $current : navigation::current();
			self::$global = navigation::$page;
		}
	}	
	
	/* 
	########################################
	####### CONFIGURATION DES ASSETS #######
	########################################
	*/

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
			//foreach($bootstrap_modules as $bs) self::$availableAPI[str_replace('bootstrap', 'bs', f::name($bs, true))] = $bs;
		}
		if(config::get('modernizr')) $scripts['*'] = a::inject($scripts['*'], 0, 'modernizr');
		
		##################
		# GETTING ASSETS #
		##################		
				
		$templates = isset($switcher) ? ','.implode(',', $switcher->returnList()) : NULL;
		$allowed_files = '{' .self::$css. '/*.css,' .self::$js. '/*.js}';
		$files = glob('{' .PATH_COMMON. ',' .PATH_CERBERUS. '}' .$allowed_files, GLOB_BRACE);
		
		foreach($files as $path)
		{
			$basename = f::name($path, true);
			self::$paths[$basename][] = $path;
		}
		foreach(self::$availableAPI as $s => $p)
		{
			if(isset(self::$paths[$p], self::$paths[$s])) self::$paths[$s] = array_merge(self::$paths[$s], self::$paths[$p]);
			elseif(isset(self::$paths[$p]) and !isset(self::$paths[$s])) self::$paths[$s] = self::$paths[$p];
			else self::$paths[$s][] = $p;
			self::$paths = a::remove(self::$paths, $p);
		}
		
		################
		# ASKED ASSETS #
		################		
		
		// Récupération des différents scripts
		self::$scripts = self::unpack($scripts);
		if(self::$scripts) foreach(self::$scripts as $key => $value)
		{
			// Bootstrap
			if($value == 'bsjs')
				self::$JS['url'] = array_merge(self::$JS['url'], $bootstrap_modules);
			
			if(isset(self::$paths[$value]))
				foreach(self::$paths[$value] as $script)
				{
					$extension = strtoupper(f::extension($script));
					if(str::find(array('http', self::$js.'/bootstrap-'), $script)) self::${$extension}['url'][] = $script;
					else self::${$extension}['min'][] = $script;
				}
		}
		return self::$scripts;
	}

	/**
	 * Sort and filters an array of asked modules
	 * 
	 * @param array 	$modules An array containing the wanted modules globally
	 * @return array  Only the needed modules in all those given
	 */
	static function unpack($modules)
	{
		// Séparation des groupes
		foreach($modules as $key => $value)
		{
			if(str::find(',', $key))
			{
				$keys = explode(',', $key);
				foreach($keys as $pages)
				{
					if(!is_array($value)) $value = array($value);
					$modules[$pages] = (isset($modules[$pages]))
						? array_merge($value, $modules[$pages])
						: $value;
				}
				$modules = a::remove($modules, $key);
			}
		}
		
		// Récupération des scripts concernés
		$modules['*'] = a::force_array($modules['*']);
		$modules[self::$global] = a::force_array($modules[self::$global]);
		$modules[self::$current] = a::force_array($modules[self::$current]);
		
		$assets = array_merge($modules['*'], $modules[self::$global], $modules[self::$current]);
		$assets = array_unique(array_filter($assets));
		
		// Suppressions des fonctions non voulues
		foreach($assets as $key => $value)
		{
			if(str::find('!', $value))
			{
				$assets = a::remove_value($assets,  substr($value, 1));
				$assets = a::remove($assets, $key);
			}
		}
		return !empty($assets) ? $assets : FALSE;
	}
	
	/* 
	########################################
	########## EXPORT DES SCRIPTS ##########
	########################################
	*/
	
	/**
	 * Returns a given path, replacing all keys (ex: {assets}) by their configured path
	 * Example : {assets}/{common}/{css}/ => assets/my_theme/stylesheets
	 * 
	 * @param string    $path The path to format
	 * @return string   The formatted path
	 */
	static function path($path)
	{
		preg_match_all('#\{([a-z]+)\}#', $path, $results);
		if($results) foreach($results[0] as $id => $r)
		{
			$variable = a::get($results[1], $id);
			$variable = self::${$variable};
			
			$path = str_replace($r, $variable, $path);
		}
		return $path;
	}
	
	/**
	 * Tells if a given script is used on the current page or not
	 * 
	 * @param string 	  The name of a script
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
	 * @param string  $place Whether to put the new ressources before or after the usual calls
	 */
	static function inject($type, $scripts, $place = 'after')
	{
		$type = str::upper($type);
		if(!is_array($scripts)) $scripts = array($scripts);
		
		foreach($scripts as $script)
		{
			if(isset(self::$paths[$script]))
				foreach(self::$paths[$script] as $s)
				{
					$extension = strtoupper(f::extension($s));
					self::${$extension}['min'][] = $s;
				}
			
			$script = preg_replace('#(<script type="text/javascript">|</script>|<style type="text/css">|</style>)#', NULL, $script);
			$is_http = str::find('http', substr($script, 0, 4));
			if(in_array(f::extension($script), array('css', 'js')) or $is_http)
			{
				if($is_http) self::${$type}['url'][] = $script;
				else self::${$type}['min'][] = $script;
			}
			else self::${$type}['inline'][$place][] = trim($script);
		}
	}
	
	/**
	 * Cleans an array of asset from duplicates and empty strings
	 * 
	 * @param array 	$array The array to sanitize
	 * @return array 	The sanitized array
	 */
	static function sanitize($array)
	{
		if(isset($array['min']) and !empty($array['min']))
		{
			$minify = array_unique(array_filter($array['min']));
			if(!is_dir('min') or !config::get('minify', TRUE) or !$minify)
				$array['url'] = array_merge($array['url'], $minify);
			else
				$array['url'][] = 'min/?f=' .implode(',', $minify). '&12345';	
		}
		$array['url'] = array_unique(array_filter($array['url']));
		
		return $array;
	}
	
	/**
	 * Fetch the current CSS styles for the page
	 */
	static function getCSS()
	{
		if(a::array_empty(self::$CSS)) self::assets();
		self::$CSS = self::sanitize(self::$CSS);
		
		if(self::$CSS['inline']['before']) echo "\t".'<style type="text/css">' .implode("\n", self::$CSS['inline']['before']). '</style>'.PHP_EOL;
		if(self::$CSS['url']) foreach(self::$CSS['url'] as $url) echo "\t".'<link rel="stylesheet" type="text/css" href="' .$url. '" />'.PHP_EOL;	
		if(self::$CSS['inline']['after']) echo "\t".'<style type="text/css">' .implode("\n", self::$CSS['inline']['after']). '</style>'.PHP_EOL;
	}

	/**
	 * Fetch the current JS scripts for the page
	 */
	static function getJS()
	{
		if(isset(self::$typekit)) self::addJS('http://use.typekit.com/' .self::$typekit. '.js');
		self::$JS = self::sanitize(self::$JS);
		
		if(self::$JS['inline']['before']) echo '<script type="text/javascript">' .PHP_EOL.implode("\n", self::$JS['inline']['before']).PHP_EOL. '</script>'.PHP_EOL;
		if(self::$JS['url']) foreach(self::$JS['url'] as $url) echo '<script type="text/javascript" src="' .$url. '"></script>' .PHP_EOL;
		if(self::$JS['inline']['after']) echo '<script type="text/javascript">' .PHP_EOL.implode("\n", self::$JS['inline']['after']).PHP_EOL. '</script>'.PHP_EOL;
	}
	
	// Raccourcis
	
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
	static function addJSBefore($javascript)
	{
		self::inject('js', $javascript, 'before');
	}
	
	static function addJS($javascript, $place = 'after')
	{
		self::inject('js', $javascript, $place);
	}
	
	/* 
	########################################
	########## MODULES ET API ##############
	########################################
	*/
	
	static function compass($config = array())
	{
		if(!file_exists(PATH_CERBERUS.self::$compass) or !file_exists(self::$compass))
		{
			$file = NULL;
			$configuration = array(
				'images_dir' => self::$images,
				'css_dir' => self::$css,
				'javascripts_dir' => self::$js,
				'fonts_dir' => self::$fonts,
				'output_style' => ':expanded',
				'preferred_syntax' => ':sass',
				'line_comments' => 'false',
				'relative_assets' => 'true');
			$configuration = array_merge($config, $configuration);
			$extensions = array('compass-recipes', 'susy', 'animation', 'rgbapng');
			
			// Configuration
			foreach($configuration as $k => $v)
			{
				if(is_array($v)) $v = json_encode($v);
				elseif(!($v == 'true' or $v == 'false' or (substr($v, 0, 1) == ':'))) $v = '"' .$v. '"';
				$file .= $k. ' = ' .$v.PHP_EOL;
			}

			// Extensions
			$file .= PHP_EOL.'# Extensions'.PHP_EOL;
			foreach($extensions as $e) $file .= "require '" .$e. "'".PHP_EOL;
			
			f::write(PATH_CERBERUS.self::$compass, $file);
			f::write(self::$compass, 'project_path = "' .substr(PATH_COMMON, 0, -1). '"'.PHP_EOL.$file);	
		}	
	}
	
	// Ajoute un plugin quelconque
	static function plugin($plugin, $selector = NULL, $params = NULL)
	{
		if(is_array($params)) $params = json_encode($params);
		$string = $plugin. '(' .$params. ')';
		if($selector) $string = '$("' .$selector.'").'.$string.';';
		dispatch::addJS($string);
	}
	
	/* Ajout un élément Google Analytics */
	static function analytics($analytics = 'XXXXX-X')
	{
		self::addJS("var _gaq=[['_setAccount','UA-" .$analytics. "'],['_trackPageview']];(function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];g.src='//www.google-analytics.com/ga.js';s.parentNode.insertBefore(g,s)}(document,'script'))");
	}
	
	/* Ajoute de polices via @font-face */
	static function typekit($kit = 'xky6uxx')
	{
		self::$typekit = $kit;
		self::addJS('try{Typekit.load();}catch(e){}');
	}
	static function googleFonts()
	{
		$fonts = func_get_args();
		
		$fonts = implode('|', $fonts);
		$fonts = str_replace(' ' , '+', $fonts);
		self::addCSS('http://fonts.googleapis.com/css?family=' .$fonts);
	}
}
?>