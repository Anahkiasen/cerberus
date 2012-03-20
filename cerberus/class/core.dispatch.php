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
	static private $LESS;
		
	/* API disponibles */
	static private $typekit;
	static private $availableAPI = array(
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'lesscss' => 'https://raw.github.com/cloudhead/less.js/master/dist/less-1.2.2.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
		'tablesorter' => 'jquery.tablesorter.min',
		'nivoslider' => 'jquery.nivo.slider.pack',
		'colorbox' => 'jquery.colorbox-min',
		'easing' => 'jquery.easing');
		
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
		self::$minify = (is_dir('min') and config::get('minify', TRUE));
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
		if(!$path_common)
		{
			$path_common =    f::path(dispatch::path('{assets}/{common}/'), f::path(dispatch::path('{assets}/'), '/'));
			$path_cerberus =  f::path(dispatch::path('{assets}/{cerberus}/'), f::path(dispatch::path('{assets}/'), '/'));
			$path_file =      f::path(dispatch::path('{assets}/{common}/{file}/'), f::path(dispatch::path('{assets}/{file}/'), f::path(dispatch::path('{file}/'))));
			
			config::hardcode('path.common', $path_common);
			config::hardcode('path.cerberus', $path_cerberus);
			config::hardcode('path.file', $path_file);
			
			f::remove('config.rb');
		}
		
		define('PATH_COMMON', $path_common);
		define('PATH_CERBERUS', $path_cerberus);
		define('PATH_FILE', $path_file);
		define('PATH_CACHE', $path_cache);
		
		self::compass();
	}
		
	/* 
	########################################
	###### TRI DES DIFFERENTS ARRAYS #######
	########################################
	*/
	
	/**
	 * Sort and filters an array of asked modules
	 * 
	 * @param array 	$modules An array containing the wanted modules globally
	 * @return array  Only the needed modules in all those given
	 */
	static function dispatchArray($modules)
	{
		if(!isset(self::$global))
		{
			self::$current = (!empty($current)) ? $current : navigation::current();
			self::$global = navigation::$page;
		}	
		
		// Séparation des groupes
		foreach($modules as $key => $value)
		{
			$modules[$key] = $value = a::force_array($modules[$key]);
			if(str::find(',', $key))
			{
				$keys = explode(',', $key);
				foreach($keys as $pages)
				{
					$modules[$pages] = (isset($modules[$pages]))
						? array_merge($value, $modules[$pages])
						: $value;
				}
				unset($modules[$key]);
			}
		}
		
		// Récupération des scripts concernés
		$modules['*'] = a::force_array($modules['*']);
		$modules[self::$global] = a::force_array($modules[self::$global]);
		$modules[self::$current] = a::force_array($modules[self::$current]);
		$arrayModules = array_merge($modules['*'], $modules[self::$global], $modules[self::$current]);
		
		// Suppressions des fonctions non voulues
		foreach($arrayModules as $key => $value)
		{
			if(str::find('!', $value))
			{
				unset($arrayModules[array_search(substr($value, 1), $arrayModules)]);
				unset($arrayModules[$key]);
			}
		}
		
		// Distinction avec les modules coeur
		if(isset(self::$cacheCore)) $arrayModules = array_values(array_diff($arrayModules, self::$cacheCore));

		if(!empty($arrayModules)) return $arrayModules;
		else return FALSE;
	}

	/**
	 * Sets the different PHP scripts for the different pages
	 * 
	 * @param array 	$modules The wanted modules
	 */
	static function setPHP($modules)
	{
		if(!is_array($modules)) $modules = array('*' => func_get_args());
		$modules = self::dispatchArray($modules);
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
		
		// Bootstrap
		$bootstrap = glob(PATH_CERBERUS.'js/bootstrap-*.js');
		foreach($bootstrap as $bs) self::$availableAPI['bs' .substr(basename($bs), 9, -3)] = $bs;
		
		// Swotcher
		$path = (isset($switcher)) ? $switcher->current() : NULL;
				
		// Mise en array des différents scripts
		if(!is_array($scripts)) $scripts = array('*' => func_get_args());
		$scripts['*'] = a::force_array($scripts['*']);
		$scripts[self::$current] = a::force_array($scripts[self::$current]);
		$scripts[self::$global] = a::force_array($scripts[self::$global]);
				
		// Fichiers par défaut
		$scripts['*'][] = 'core';
		if(config::get('bootstrap')) $scripts['*'] = a::inject($scripts['*'], 0, 'bootstrap');
		$scripts['*'] += array(99 => 'styles');
		
		$scripts[self::$current][] = self::$current;
		$scripts[self::$global][] = self::$global;

		// Préparation de l'array des scripts disponibles
		$templates = isset($switcher) ? ','.implode(',', $switcher->returnList()) : NULL;
		$allowed_files = '{css/*.css,js/*.js}';
		if(PATH_COMMON == self::path('{assets}/{common}/')) $allowed_folders = dispatch::path('{assets}/{{common},{cerberus}' .$templates. '}/');
		elseif(PATH_COMMON == self::path('{assets}/')) $allowed_folders = dispatch::path('{{assets},{assets}/{cerberus}}/');
		else $allowed_folders = '/';
		
		$files = glob($allowed_folders.$allowed_files, GLOB_BRACE);
		foreach($files as $path)
		{
			$basename = f::name($path, true);
			if(!isset($dispath[$basename])) $dispath[$basename] = array();
			array_push($dispath[$basename], $path);
		}
		$dispath['bootstrap'] = array(PATH_CERBERUS. 'css/bootstrap.css');
		
		// Récupération des différents scripts
		self::$scripts = array_filter(self::dispatchArray($scripts));
		if(self::$scripts) foreach(self::$scripts as $key => $value)
		{
			if(!empty($value))
			{
				// Bootstrap
				if($value == 'bootstrapjs')
					self::$JS['url'] = array_merge(self::$JS['url'], $bootstrap);
				
				// API
				if(isset(self::$availableAPI[$value]))
				{
					$API = self::$availableAPI[$value];
					if(isset($dispath[$value])) self::$CSS['min'] = array_merge(self::$CSS['min'], $dispath[$value]); // CSS annexe
					if(str::find(array('http', 'bootstrap'), $API)) self::$JS['url'][] = $API;
					else self::$JS['min'][] = f::path(PATH_CERBERUS. 'js/' .$API. '.js', f::path(PATH_COMMON.'js/'.$API.'.js', $API));
				}
				else
				{
					if(isset($dispath[$value]))
						foreach($dispath[$value] as $script)
						{
							$extension = strtoupper(f::extension($script));
							self::${$extension}['min'][] = $script;
						}
				}
			}
			else unset(self::$scripts[$key]);
		}
		return self::$scripts;
	}
	
	/* 
	########################################
	########## EXPORT DES SCRIPTS ##########
	########################################
	*/
	
	/* Calcule un chemin donné */
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
	 * @param string 	 The name of a script
	 * @return boolean Wether or not the script is used on this page
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
	 * @param string  $place Wether to put the new ressources before or after the usual calls
	 */
	static function inject($type, $scripts, $place = 'after')
	{
		$type = str::upper($type);
		if(!is_array($scripts)) $scripts = array($scripts);
		
		foreach($scripts as $script)
		{
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
			if(!self::$minify or !$minify)
				$array['url'] = array_merge($array['url'], $minify);
			else
				$array['url'][] = 'min/?f=' .implode(',', $minify);	
		}
		$array['url'] = array_unique(array_filter($array['url']));
		
		return $array;
	}
	
	/**
	 * Fetch the current CSS styles for the page
	 */
	static function getCSS()
	{
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
		if(!file_exists(PATH_CERBERUS.self::$compass) or !file_exists(PATH_COMMON.self::$compass))
		{
			$file = NULL;
			$array = array(
				'images_dir' => self::$images,
				'css_dir' => self::$css,
				'javascripts_dir' => self::$js,
				'fonts_dir' => self::$fonts,
				'output_style' => ':expanded',
				'preferred_syntax' => ':sass',
				'line_comments' => 'false',
				'relative_assets' => 'true' );
			$config = array_merge($config, $array);
			
			foreach($config as $k => $v)
			{
				if(!($v == 'true' or $v == 'false' or (substr($v, 0, 1) == ':'))) $v = '"' .$v. '"'; 
				$file .= $k. ' = ' .$v.PHP_EOL;
			}
			
			f::write(PATH_CERBERUS.self::$compass, $file);
			f::write(PATH_COMMON.self::$compass, $file);	
		}	
	}
	
	/* Ajout un élément Google Analytics */
	static function analytics($analytics = 'XXXXX-X')
	{
		self::addJS(
		"var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-" .$analytics. "']);
		_gaq.push(['_trackPageview']);
		
		(function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();");
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