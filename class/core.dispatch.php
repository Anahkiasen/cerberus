<?php
/* 
########################################
########## CLASSE DISPATCH #############
########################################
*/

class dispatch extends Cerberus
{
	private $current;
	private $CSS;
	private $JS;
	
	// Initilisation de Dispatch
	function __construct($current = NULL)
	{	
		global $desired;
		
		// Page en cours
		if(!empty($current)) $this->current = $current;
		else
		{
			if($desired->page == 'admin' and isset($_GET['admin'])) $this->current = $desired->page. '-' .get('admin');
			else $this->current = $desired->current();
		}
		
		$explode = explode('-', $this->current);
		$this->global = $explode[0];
	}
	
	// Tri et répartition des modules demandés
	function dispatchArray($modules)
	{
		$arrayGlobal =
		$arrayModules =
		$arraySubmodules = array();
			
		// Séparation des groupes
		foreach($modules as $key => $value)
		{
			$value = a::force_array($value);
			if(strpos($key, ',') != FALSE)
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

		// Véritifcation de la présence de modules concernés (pagesouspage/page/*)
		if(isset($modules[$this->current])) $arrayModules = a::force_array($modules[$this->current]);
		if(isset($modules[$this->global])) $arraySubmodules = a::force_array($modules[$this->global]);
		if(isset($modules['*'])) $arrayGlobal = a::force_array($modules['*']);
		$arrayModules = array_merge($arrayGlobal, $arrayModules, $arraySubmodules);
		
		// Suppressions des fonctions non voulues
		foreach($arrayModules as $key => $value)
		{
			if(findString('!', $value))
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
	function getAPI($scripts = NULL)
	{
		global $switcher, $desired;

		// API
		$availableAPI = array(
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'colorbox' => 'jquery.colorbox-min',
		'nivoslider' => 'jquery.nivo.slider.pack');
		
		if(isset($switcher)) $path = $switcher->path();
		$defaultCSS = 'css/styles.css';
		$defaultJS = 'js/core.js';
		$this->JS = $this->CSS = array();
		
		// Fichiers par défaut
		if(!$scripts) $scripts = array();
		a::force_array($scripts['*']);
		$scripts['*'][] = 'assets/css/cerberus.css';
		$scripts['*'][] = 'assets/'.$defaultCSS;
		$scripts['*'][] = 'assets/'.$defaultJS;
		
		// Fichiers spécifiques aux pages
		a::force_array($scripts[$this->current]);
		a::force_array($scripts[$this->global]);
		
		$scripts[$this->current][] = $this->current;
		$scripts[$this->global][] = $this->global;
		
		// Fichiers switch
		if(isset($path))
		{
			$scripts['*'][] = $path.$defaultCSS;
			$scripts['*'][] = $path.$defaultJS;
		}
		$this->scripts = $scripts = $this->dispatchArray($scripts);
		if($scripts)
		{
			foreach($scripts as $value) 
			{
				if(!empty($value))
				{
					$thisScript = $value;

					// Si le script est présent dans les prédéfinis
					if(isset($availableAPI[$value]))
					{
						$this->CSS['min'][] = f::sexist('assets/css/' .$thisScript. '.css'); // CSS annexe
						if(findString('http', $availableAPI[$value])) $this->JS['url'][] = $availableAPI[$value];
						else $this->JS['min'][] = f::sexist('assets/js/' .$availableAPI[$value]. '.js');
					}
					
					// Si le chemin est spécifié manuellement
					elseif(findString('.js', $thisScript)) $this->JS['min'][] = f::sexist($thisScript);
					elseif(findString('.css', $thisScript)) $this->CSS['min'][] = f::sexist($thisScript);
					
					// Sinon on vérifie la présence du script dans les fichiers
					else
					{
						$this->JS['min'][] = f::sexist('assets/js/' .$thisScript. '.js');
						$this->CSS['min'][] = f::sexist('assets/css/' .$thisScript. '.css');
						if(isset($path))
						{
							$this->JS['min'][] = f::sexist($path.'js/' .$thisScript. '.js');
							$this->CSS['min'][] = f::sexist($path.'css/' .$thisScript. '.css');
						}
					}
				}
			}
			
			return $scripts;
		}	
	}
	
	// Script activé ou non
	function isScript($script)
	{
		return (isset($this->scripts) and in_array($script, $this->scripts));
	}
	
	// Affichage des scripts
	function getCSS()
	{
		if($this->CSS['min']) 
		{
			$minify = array_filter($this->CSS['min']);
			if($minify) $this->CSS['url'][] = 'min/?f=' .implode(',', $minify);
		}
		if(!empty($this->CSS['url'])) echo '<link rel="stylesheet" type="text/css" href="' .implode('" /><link rel="stylesheet" type="text/css" href="', $this->CSS['url']). '" />' . "\n";		
		if(isset($this->CSS['inline'])) echo '<style type="text/css">' .implode("\n", $this->CSS['inline']). '</style>' . "\n";
	}
	function getJS()
	{
		if($this->JS['min']) 
		{
			$minify = array_filter($this->JS['min']);
			if($minify) $this->JS['url'][] = 'min/?f=' .implode(',', $minify);
		}
		if(!empty($this->JS['url'])) echo '<script type="text/javascript" src="' .implode('"></script>' . "\n". '<script type="text/javascript" src="', $this->JS['url']). '"></script>' . "\n";		
		if(isset($this->JS['inline'])) echo '<script type="text/javascript">' .implode("\n", $this->JS['inline']). '</script>' . "\n";
	}
	
	// Ajout de scripts à la volée
	function addJS()
	{
		$args = func_get_args();
		$javascript = $args[0];
		$javascript = str_replace('<script type="text/javascript">', '', $javascript);
		$javascript = str_replace('</script>', '', $javascript);
		
		if(isset($args[1])) $this->JS['inline'][$args[1]] = $javascript;
		else $this->JS['inline'][] = $javascript;
	}
}
?>