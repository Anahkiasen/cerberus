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
		
	static private $typekit;
	static private $paths = array();
	
	/* Raccourcis et aliases */
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
		
	/* Plugins et submodules */
	static private $plugins_files = array(
		'chosen'      => array(
			'chosen/chosen/chosen.css',
			'chosen/chosen/chosen.jquery.min.js',
			'chosen/chosen/chosen-sprite.png'),
		'colorbox'    => array(
			'colorbox/colorbox/jquery.colorbox.js'),
		'modernizr'   => array(
			'modernizr/modernizr.js'),
		'noty'        => array(
			'noty/css/jquery.noty.css',
			'noty/css/noty_theme_twitter.css',
			'noty/js/jquery.noty.js'),
		'tablesorter' => array(
			'tablesorter/js/jquery.tablesorter.min.js'),
			);
		
	/***************************
	 ** RESSOURCES ET CHEMINS **
	 ***************************/
	
	static public $compass  = 'config.rb';
	
	static public $assets   = 'assets';
	static public $cerberus = 'cerberus';
	static public $common   = 'common';
	static public $plugins  = 'plugins';
	
	static public $coffee   = 'coffee';
	static public $css      = 'css';
	static public $file     = 'file';
	static public $fonts    = 'fonts';
	static public $images   = 'img';
	static public $js       = 'js';
	static public $sass     = 'sass';
	static public $swf      = 'swf';
	
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
		
		// Chemins par défaut
		if(!$path_common or !file_exists($path_common))
		{
			$path_common =    f::path(self::path('{assets}/{common}/'),        f::path(self::path('{assets}/'), ''));
			$path_cerberus =  f::path(self::path('{assets}/{cerberus}/'),      f::path(self::path('{assets}/'), ''));
			$path_file =      f::path(self::path('{assets}/{common}/{file}/'), f::path(self::path('{assets}/{file}/'), f::path(self::path('{file}/'))));
			
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
		
		if(LOCAL) self::compass();
	}
	
	// Configure les indexs
	private static function index()
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
		}
		if(config::get('modernizr')) $scripts['*'] = a::inject($scripts['*'], 0, 'modernizr');
		
		##################
		# GETTING ASSETS #
		##################		
				
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
		
		// Récupération des fichiers voulus dans les plugins
		foreach(self::$plugins_files as $plugin => $plugin_files)
		{
			if(isset(self::$paths[$plugin])) continue;
			
			foreach($plugin_files as $key => $value)
			{
				$type = f::type($value);
				$extension = ($type == 'image') ? 'img' : f::extension($value);
				$new_path = PATH_CERBERUS.$extension. '/plugins/' .basename($value);
				
				copy('assets/plugins/' .$value, $new_path);
				$plugin_files[$key] = $new_path;
			}
			self::$paths[$plugin] = $plugin_files;
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
					if($extension != 'CSS' and $extension != 'JS') continue;
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
	private static function unpack($modules)
	{
		// Séparation des groupes
		foreach($modules as $key => $value)
		{
			$value = a::force_array($value);
			if(str::find(',', $key))
			{
				$keys = explode(',', $key);
				foreach($keys as $pages)
				{
					if(!is_array($value)) $value = array($value);
					$modules[$pages] = (isset($modules[$pages]))
						? array_merge($value, a::force_array($modules[$pages]))
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
	
	static function currentCSS()
	{
		return self::$CSS['url'];
	}
	
	static function currentJS()
	{
		return self::$JS['url'];
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
			$script = preg_replace('#(<script type="text/javascript">|</script>|<style type="text/css">|</style>)#', NULL, $script);
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
		if(self::$JS['url']) foreach(self::$JS['url'] as $url) $head[] = '<script type="text/javascript" src="' .$url. '"></script>';
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
			$extensions = config::get('compass');
			
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
		self::addJS($string);
	}
	
	/* Ajout un élément Google Analytics */
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
	
	// GoogleFonts
	static function googleFonts()
	{
		$fonts = func_get_args();
		
		$fonts = implode('|', $fonts);
		$fonts = str_replace(' ' , '+', $fonts);
		$fonts = str_replace('*' , '100,200,300,400,500,600,700,800,900', $fonts);
		
		self::addCSS('http://fonts.googleapis.com/css?family=' .$fonts);
	}
}
?>