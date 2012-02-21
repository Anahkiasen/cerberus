<?php
class dispatch extends Cerberus
{
	private $current;
	private $CSS;
	private $JS;
	
	// Initilisation de Dispatch
	function __construct($current = NULL)
	{			
		// Page en cours
		$this->current = (!empty($current)) ? $current : navigation::current();
		$this->global = navigation::current_page();
		$this->minify = is_dir('min');
	}
		
	/* 
	########################################
	###### TRI DES DIFFERENTS ARRAYS #######
	########################################
	*/
	
	// Tri et répartition des modules demandés
	function dispatchArray($modules)
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
		$modules[$this->global] = a::force_array($modules[$this->global]);
		$modules[$this->current] = a::force_array($modules[$this->current]);
		$arrayModules = array_merge($modules['*'], $modules[$this->global], $modules[$this->current]);
		
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
		if(isset($this->cacheCore)) $arrayModules = array_values(array_diff($arrayModules, $this->cacheCore));

		if(!empty($arrayModules)) return $arrayModules;
		else return FALSE;
	}

	// Modules PHP
	function getPHP($modules)
	{
		$modules = $this->dispatchArray($modules);
		if($modules) new Cerberus($modules, get('page', 'home'));
	}
	
	// Modules JS/CSS
	function getAPI($scripts = array())
	{
		global $switcher;

		// API
		$this->availableAPI = array(
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'lesscss' => 'https://raw.github.com/cloudhead/less.js/master/dist/less-1.2.2.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
		'tablesorter' => 'jquery.tablesorter.min',
		'nivoslider' => 'jquery.nivo.slider.pack',
		'colorbox' => 'jquery.colorbox-min',
		'easing' => 'jquery.easing');
		
		// bootstrap
		$bootstrap = glob(PATH_CERBERUS.'js/bootstrap-*.js');
		foreach($bootstrap as $bs) $this->availableAPI['bs' .substr(basename($bs), 9, -3)] = $bs;
		
		$this->JS = $this->CSS = $this->LESS = array('min' => array(), 'url' => array(), 'inline' => array());
		$path = (isset($switcher)) ? $switcher->current() : NULL;
				
		// Mise en array des différents scripts
		$scripts['*'] = a::force_array($scripts['*']);
		$scripts[$this->current] = a::force_array($scripts[$this->current]);
		$scripts[$this->global] = a::force_array($scripts[$this->global]);
				
		// Fichiers par défaut
		$scripts['*'][] = 'core';
		$scripts['*'] += config::get('bootstrap', TRUE)
			? array(99 => 'bootstrap')
			: array(99 => 'styles');
		
		$scripts[$this->current][] = $this->current;
		$scripts[$this->global][] = $this->global;

		// Préparation de l'array des scripts disponibles
		$templates = isset($switcher) ? ','.implode(',', $switcher->returnList()) : NULL;
		$allowed_files = '{css/*.{css,less},less/*.{css,less},js/*.js}';
		if(PATH_COMMON == 'assets/common/') $allowed_folders = 'assets/{common,cerberus' .$templates. '}/';
		elseif(PATH_COMMON == 'assets/') $allowed_folders = 'assets/';
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
		$this->scripts = array_filter($this->dispatchArray($scripts));
		if($this->scripts) foreach($this->scripts as $key => $value)
		{
			if(!empty($value))
			{
				// Bootstrap
				if($value == 'bootstrapjs')
					$this->JS['url'] = array_merge($this->JS['url'], $bootstrap);
				
				// API
				if(isset($this->availableAPI[$value]))
				{
					$API = $this->availableAPI[$value];
					if(isset($dispath[$value])) $this->CSS['min'] = array_merge($this->CSS['min'], $dispath[$value]); // CSS annexe
					if(str::find(array('http', 'bootstrap'), $API)) $this->JS['url'][] = $API;
					else $this->JS['min'][] = f::path(PATH_CERBERUS. 'js/' .$API. '.js', f::path(PATH_COMMON.'js/'.$API.'.js', $API));
				}
				else
				{
					if(isset($dispath[$value]))
						foreach($dispath[$value] as $script)
						{
							$extension = strtoupper(f::extension($script));
							$this->{$extension}['min'][] = $script;
						}
				}
			}
			else unset($this->scripts[$key]);
		}
		return $this->scripts;
	}
	
	/* 
	########################################
	########## EXPORT DES SCRIPTS ##########
	########################################
	*/
	
	// Script activé ou non
	function isScript($script)
	{
		return (isset($this->scripts) and in_array($script, $this->scripts));
	}
	
	// Affichage des scripts
	function getCSS()
	{
		// LESS
		if(isset($this->LESS['min']) and (LOCAL or empty($this->CSS['min'])))
		{
			$minify = array_unique(array_filter($this->LESS['min'])); 
			foreach($minify as $thisfile)
			{
				echo "\t".'<link rel="stylesheet/less" type="text/css" href="' .$thisfile. '" />'. PHP_EOL;
				$this->CSS['min'] = a::remove($this->CSS['min'], strtr($thisfile, array('.less' => '.css', 'less/' => 'css/')), false);
			}
			echo "\t".'<script type="text/javascript" src="' .$this->availableAPI['lesscss']. '"></script>'.PHP_EOL;
			//echo "\t".'<script type="text/javascript"> less.watch() </script>'.PHP_EOL;
		}
		
		// CSS
		if($this->CSS['min'])
		{
			$minify = array_unique(array_filter($this->CSS['min']));
			if($this->minify) { if($minify) $this->CSS['url'][] = 'min/?f=' .implode(',', $minify); }
			else { if($minify) $this->CSS['url'] = array_merge($this->CSS['url'], $minify); }
		}
		if($this->CSS['url']) foreach($this->CSS['url'] as $url) echo "\t".'<link rel="stylesheet" type="text/css" href="' .$url. '" />'.PHP_EOL;	
		if($this->CSS['inline']) echo "\t".'<style type="text/css">' .implode("\n", $this->CSS['inline']). '</style>'.PHP_EOL;
	}
	function getJS()
	{
		if(isset($this->typekit)) $this->addJS('http://use.typekit.com/' .$this->typekit. '.js');
		if($this->JS['min'])
		{
			$minify = array_unique(array_filter($this->JS['min']));	
			if($this->minify) { if($minify) $this->JS['url'][] = 'min/?f=' .implode(',', $minify); }
			else if($this->JS['url']) $this->JS['url'] = array_merge($this->JS['url'], $minify);
		}
		if($this->JS['url'])
		{
			$this->JS['url'] = array_unique(array_filter($this->JS['url']));
			foreach($this->JS['url'] as $url) echo '<script type="text/javascript" src="' .$url. '"></script>' .PHP_EOL;
		}
		if($this->JS['inline']) echo '<script type="text/javascript">' .PHP_EOL.implode("\n", $this->JS['inline']).PHP_EOL. '</script>'.PHP_EOL;
	}
	
	/* 
	########################################
	########## RAJOUT GLOBAL ###############
	########################################
	*/
	
	// Ajoute une feuille de style
	function addCSS($link)
	{
		$array = str::find('.less', $link) ? 'LESS' : 'CSS';
		
		if(str::find('http', $link)) $this->{$array}['url'][] = $link;
		else $this->{$array}['min'][] = $link;
	}
	
	// Ajout de scripts à la volée
	function addJS()
	{
		$args = func_get_args();
		$javascript = $args[0];
		$javascript = str_replace('<script type="text/javascript">', '', $javascript);
		$javascript = str_replace('</script>', '', $javascript);
		
		if(str::find('http', $javascript)) $this->JS['url'][] = $javascript;
		else $this->JS['inline'][] = $javascript;
	}
	
	// Ajout un élément Google Analytics
	function analytics($analytics = 'XXXXX-X')
	{
		$this->addJS("
		var _gaq=[['_setAccount','UA-" .$analytics. "'],['_trackPageview']];
	    (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
	    g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
	    s.parentNode.insertBefore(g,s)}(document,'script'));");
	}
	
	// Ajoute de polices via @font-face
	function typekit($kit = 'xky6uxx')
	{
		$this->typekit = $kit;
		$this->addJS('try{Typekit.load();}catch(e){}');
	}
	function googleFonts()
	{
		$fonts = func_get_args();
		
		$fonts = implode('|', $fonts);
		$fonts = str_replace(' ' , '+', $fonts);
		$this->addCSS('http://fonts.googleapis.com/css?family=' .$fonts);
	}
}
?>