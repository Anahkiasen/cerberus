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
		'colorbox' => 'jquery.colorbox.min',
		'easing' => 'jquery.easing');
	
	// Initilisation de Dispatch
	function __construct($current = NULL)
	{			
		// Page en cours
		self::$current = (!empty($current)) ? $current : navigation::current();
		self::$global = navigation::$page;
		self::$minify = is_dir('min');
		self::$JS = self::$CSS = self::$LESS =
			array('min' => array(), 'url' => array(), 'inline' => array());
	}
		
	/* 
	########################################
	###### TRI DES DIFFERENTS ARRAYS #######
	########################################
	*/
	
	/* Tri et répartition des modules demandés */
	static function dispatchArray($modules)
	{
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

	/* Modules PHP */
	static function setPHP($modules)
	{
		if(!is_array($modules)) $modules = array('*' => func_get_args());
		$modules = self::dispatchArray($modules);
		if($modules) new Cerberus($modules, get('page', 'home'));
	}
	
	/* Modules JS/CSS */
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
		$scripts['*'] += array(98 => 'bootstrap', 99 => 'styles');
		
		$scripts[self::$current][] = self::$current;
		$scripts[self::$global][] = self::$global;

		// Préparation de l'array des scripts disponibles
		$templates = isset($switcher) ? ','.implode(',', $switcher->returnList()) : NULL;
		$allowed_files = '{css/*.{css,less},less/*.{css,less},js/*.js}';
		if(PATH_COMMON == 'assets/common/') $allowed_folders = 'assets/{common,cerberus' .$templates. '}/';
		elseif(PATH_COMMON == 'assets/') $allowed_folders = '{assets,assets/cerberus}/';
		else $allowed_folders = '/';
		
		$files = glob($allowed_folders.$allowed_files, GLOB_BRACE);
		foreach($files as $path)
		{
			$basename = f::name($path, true);
			if(!isset($dispath[$basename])) $dispath[$basename] = array();
			array_push($dispath[$basename], $path);
		}
		$dispath['bootstrap'] = array(PATH_CERBERUS. 'less/bootstrap.less', PATH_CERBERUS. 'css/bootstrap.css');
		
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
	
	/* Script activé ou non */
	static function isScript($script)
	{
		return (isset(self::$scripts) and in_array($script, self::$scripts));
	}
	
	/* Récupération des feuilles de style */
	static function getCSS()
	{
		// LESS
		if(config::get('lesscss', FALSE) and isset(self::$LESS['min']) and !empty(self::$LESS['min']) and (LOCAL or empty(self::$CSS['min'])))
		{
			$minify = array_unique(array_filter(self::$LESS['min'])); 
			foreach($minify as $thisfile)
			{
				echo "\t".'<link rel="stylesheet/less" type="text/css" href="' .$thisfile. '" />'. PHP_EOL;
				self::$CSS['min'] = a::remove(self::$CSS['min'], strtr($thisfile, array('.less' => '.css', 'less/' => 'css/')), false);
			}
			echo "\t".'<script type="text/javascript" src="' .self::$availableAPI['lesscss']. '"></script>'.PHP_EOL;
			//echo "\t".'<script type="text/javascript"> less.watch() </script>'.PHP_EOL;
		}
		
		// CSS
		if(self::$CSS['min'])
		{
			$minify = array_unique(array_filter(self::$CSS['min']));
			if(self::$minify) { if($minify) self::$CSS['url'][] = 'min/?f=' .implode(',', $minify); }
			else { if($minify) self::$CSS['url'] = array_merge(self::$CSS['url'], $minify); }
		}
		if(self::$CSS['url']) foreach(self::$CSS['url'] as $url) echo "\t".'<link rel="stylesheet" type="text/css" href="' .$url. '" />'.PHP_EOL;	
		if(self::$CSS['inline']) echo "\t".'<style type="text/css">' .implode("\n", self::$CSS['inline']). '</style>'.PHP_EOL;
	}

	/* Récupération du Javascript */
	static function getJS()
	{
		if(isset(self::$typekit)) self::addJS('http://use.typekit.com/' .self::$typekit. '.js');
		if(self::$JS['min'])
		{
			$minify = array_unique(array_filter(self::$JS['min']));	
			if(self::$minify) { if($minify) self::$JS['url'][] = 'min/?f=' .implode(',', $minify); }
			else if(self::$JS['url']) self::$JS['url'] = array_merge(self::$JS['url'], $minify);
		}
		if(self::$JS['url'])
		{
			self::$JS['url'] = array_unique(array_filter(self::$JS['url']));
			foreach(self::$JS['url'] as $url) echo '<script type="text/javascript" src="' .$url. '"></script>' .PHP_EOL;
		}
		if(self::$JS['inline']) echo '<script type="text/javascript">' .PHP_EOL.implode("\n", self::$JS['inline']).PHP_EOL. '</script>'.PHP_EOL;
	}
	
	/* 
	########################################
	########## RAJOUT GLOBAL ###############
	########################################
	*/
	
	/* Ajoute une feuille de style */
	static function addCSS($link, $min = true)
	{
		$array = str::find('.less', $link) ? 'LESS' : 'CSS';
		
		if(str::find('http', $link) or !$min) self::${$array}['url'][] = $link;
		else self::${$array}['min'][] = $link;
	}
	
	/* Ajout de scripts à la volée */
	static function addJS($javascript)
	{
		$javascript = func_get_args();	
		
		if(sizeof($javascript) == 1)
		{
			$javascript = a::get($javascript, 0);
			$javascript = str_replace('<script type="text/javascript">', NULL, $javascript);
			$javascript = str_replace('</script>', NULL, $javascript);
			
			if(f::extension($javascript) == 'js')
			{
				if(str::find('http', substr($javascript, 0, 4))) self::$JS['url'][] = $javascript;
				else self::$JS['min'][] = $javascript;
			}
			else self::$JS['inline'][] = $javascript;
		}
		else foreach($javascript as $j) self::addJS($j);
	}
	
	/* 
	########################################
	########## MODULES ET API ##############
	########################################
	*/
	
	static function compass($config = array())
	{
		if(!file_exists('config.rb'))
		{
			$array = array(
				'css_dir' => PATH_COMMON.'css',
				'images_dir' => PATH_COMMON.'img',
				'images_dir' => PATH_COMMON.'img',
				'fonts_dir' => PATH_CERBERUS.'fonts',
				'javascripts_dir' => PATH_COMMON.'js',
				'output_style' => 'expanded',
				'additional_import_paths' => '[\'' .PATH_CERBERUS. 'scss\', \'' .PATH_COMMON. 'scss\']'
			);
			$config = array_merge($config, $array);
			
			$file = NULL;
			foreach($config as $k => $v) $file .= $k. ' = "' .$v. '"' .PHP_EOL;
			f::write('config.rb', $file);
		}	
	}
	
	/* Ajout un élément Google Analytics */
	static function analytics($analytics = 'XXXXX-X')
	{
		self::addJS(
		"var _gaq = _gaq || [];
		_gaq.push(['_setAccount', 'UA-" .$analytics. "']);
		_gaq.push(['_trackPageview']);
		
		(static function() {
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