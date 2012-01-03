<?php
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
		$this->current = (!empty($current)) ? $current : $desired->current();
		$this->global = $desired->current(false);
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
		a::force_array($modules['*']);
		a::force_array($modules[$this->global]);
		a::force_array($modules[$this->current]);
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
		global $switcher, $desired;

		// API
		$this->availableAPI = array(
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js',
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'lesscss' => 'http://lesscss.googlecode.com/files/less-1.1.5.min.js',
		'colorbox' => 'jquery.colorbox-min',
		'nivoslider' => 'jquery.nivo.slider.pack');
		
		$this->JS = $this->CSS = $this->LESS = array('min' => array());
		$path = (isset($switcher)) ? $switcher->path() : NULL;
				
		// Mise en array des différents scripts
		a::force_array($scripts['*']);
		a::force_array($scripts[$this->current]);
		a::force_array($scripts[$this->global]);
				
		// Fichiers par défaut
		array_push($scripts['*'], 'cerberus', 'styles', 'core');
		$scripts[$this->current][] = $this->current;
		$scripts[$this->global][] = $this->global;
		
		// Préparation de l'array des scripts disponibles
		$files = glob('assets/{css/*.{css,less},switch/'.$path.'/css/*.{css,less},js/*.js,switch/'.$path.'/js/*.js}', GLOB_BRACE);
		foreach($files as $path)
		{
			$basename = f::name($path, true);
			if(!isset($dispath[$basename])) $dispath[$basename] = array();
			array_push($dispath[$basename], $path);
		}

		$this->scripts = $this->dispatchArray($scripts);

		// Récupération des différents scripts
		if($this->scripts) foreach($this->scripts as $key => $value)
		{
			if(!empty($value))
			{
				// API
				if(isset($this->availableAPI[$value]))
				{
					$API = $this->availableAPI[$value];
					if(isset($dispath[$value])) $this->CSS['min'] = array_merge($this->CSS['min'], $dispath[$value]); // CSS annexe
					if(str::find('http', $API)) $this->JS['url'][] = $API;
					else $this->JS['min'][] = f::sexist('assets/js/' .$API. '.js');
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
		if((!empty($this->LESS['min']) and LOCAL) or empty($this->CSS['min']))
		{
			foreach($this->LESS['min'] as $thisfile)
			{
				echo '<link rel="stylesheet/less" type="text/css" href="' .$thisfile. '" />'. PHP_EOL;
				$this->CSS['min'] = a::splice($this->CSS['min'], str_replace('.less', '.css', $thisfile));
			}
			echo '
			<script>var less = {env: "development"};</script>
			<script src="' .$this->availableAPI['lesscss']. '" type="text/javascript"></script>' . "\n";
		}
		
		// CSS
		$minify = array_unique(array_filter($this->CSS['min']));
		if($minify) $this->CSS['url'][] = 'min/?f=' .implode(',', $minify);
		if(!empty($this->CSS['url'])) foreach($this->CSS['url'] as $url) echo '<link rel="stylesheet" type="text/css" href="' .$url. '" />' . "\n";	
		if(isset($this->CSS['inline'])) echo '<style type="text/css">' .implode("\n", $this->CSS['inline']). '</style>' . "\n";
	}
	function getJS()
	{
		$minify = array_unique(array_filter($this->JS['min']));
		if($minify) $this->JS['url'][] = 'min/?f=' .implode(',', $minify);

		if(!empty($this->JS['url'])) foreach($this->JS['url'] as $url) echo '<script type="text/javascript" src="' .$url. '"></script>' .PHP_EOL;		
		if(isset($this->JS['inline'])) echo '<script type="text/javascript">' .implode("\n", $this->JS['inline']). '</script>' . "\n";
	}
	
	/* 
	########################################
	########## RAJOUT GLOBAL ###############
	########################################
	*/
	
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