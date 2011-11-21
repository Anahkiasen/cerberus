<?php
class Cerberus
{
	/*
	########################################
	############### PREPARATION ############
	########################################
	*/
	
	// Paramètres
	private $render;
	private $erreur;
	
	// Modes
	private $mode;
				
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', TRUE));
	}	
	
	function __construct($modules, $mode = 'core')
	{
		// Modules coeur
		$this->mode = 
			($mode == 'core')
			? 'core'
			: get('page', 'home');

		// Création ou non du fichier
		if(!PRODUCTION or !file_exists('cerberus/cache/' .$this->mode. '.php'))
		{
			$this->unpackModules($modules);
			$this->generate();
		}
		
		// Include du fichier
		f::inclure('cerberus/cache/' .$this->mode. '.php');
	}	
	
	/* 
	########################################
	#### RECUPERATION DES FONCTIONS ########
	########################################
	*/
	
	// Chargement du moteur Cerberus
	function unpackModules($modules = '')
	{	
		$modules = a::beArray($modules);
		if($this->mode == 'core')
		{
			// Modules de base
			$modules = array_merge(array(
				'display', 'timthumb', 'findString'),
				$modules);
		}
				
		// Tri des modules et préparation des packs
		if(!empty($modules))
		{
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL'),
			'pack.navigation' => array('baseref', 'navigation', 'rewrite'),
			'class.admin' => array('admin', 'admin.setup'),
			'class.mail' => array('smail', 'stripHTML'),
			'class.form' => array('form', 'checkString'),
			'class.news' => array('news', 'bbcode', 'truncate'));
		
			foreach($modules as $value)
			{
				if(isset($packages[$value])) foreach($packages[$value] as $includePack) $modulesArray[] = $includePack;
				else $modulesArray[] = $value;
			}
			
			// Nettoyage de l'array des fonctions et mise en cache du core ; chargement des modules
			$modulesArray = array_unique($modulesArray);
			asort($modulesArray);
			$this->cacheCore = $modulesArray;
			
			foreach($modulesArray as $value)
				if($value) $this->loadModule($value);
		}
	}
	
	// Obtention du chemin d'un module
	function getFile($module)
	{
		$cheminsValides = array(
			'cerberus/tools/',
			'cerberus/class/',
			'assets/php/');
		
		foreach($cheminsValides as $chemin)
		{
			if(file_exists($chemin.$module.'.php')) return $chemin.$module.'.php';
			elseif(file_exists($chemin.'class.'.$module.'.php')) return $chemin.'class.'.$module.'.php';
		}
		return false;
	}		

	// Chargement d'un module
	function loadModule($module)
	{
		if(!function_exists($module) and !class_exists($module))
		{
			$fichierModule = $this->getFile($module);
			if($fichierModule)
			{
				$thisModule = trim($this->file_get_contents_utf8($fichierModule));
				$thisModule = substr($thisModule, 5, -2);
				$this->render .= $thisModule;
			}
			else $this->erreurs[] = errorHandle('Warning', 'Module ' .$module. ' non existant.', __FILE__, __LINE__);
		}
	}
		
	// Fonction Inject
	function injectModule()
	{
		$module = func_get_args();
		foreach($module as $thismodule)
		{
			if(!function_exists($thismodule) and !class_exists($thismodule))
			{
				$fichier = $this->getFile($thismodule);
				if($fichier) include($fichier);
				else errorHandle('Warning', 'Module ' .$thismodule. ' non trouvé', __FILE__, __LINE__);
			}
		}
	}
			
	/* 
	########################################
	########### RENDU DU FICHIER ###########
	########################################
	*/
	
	// Affichage des erreurs et rendu du fichier
	function generate()
	{
		if(!empty($this->erreurs))
			foreach($this->erreurs as $value) echo $value. '<br />';
		
		else
			if(!empty($this->render))
				f::write('cerberus/cache/' .$this->mode. '.php', '<?php' .$this->render. '?>');
	}
		
	/* 
	########################################
	########## FONCTIONS UTILITAIRES #######
	########################################
	*/
	
	// Fonction META 
	function meta($mode = 'meta')
	{
		global $meta, $pageVoulue, $sousPageVoulue;
		
		// Récupération des informations
		if($mode == 'meta')
		{
			if(db::is_table('structure', 'meta'))
			{
				$metadata = db::left_join('meta M', 'structure S', 'M.page = S.id', 'S.page, S.parent, M.titre, M.description, M.url', array('langue' => l::current()));
				foreach($metadata as $values)
					$meta[$values['parent'].'-'.$values['page']] = $values;
				//s::set('metadata', $meta);
			}
		}
		else
		{
			$pagenow = $pageVoulue. '-' .$sousPageVoulue;
			$default_title = ($pageVoulue == 'admin' and get('admin'))
				? 'Gestion ' .ucfirst(get('admin'))
				: l::get('menu-' .$pageVoulue);
				
			if(isset($meta[$pagenow]))
			{
				// titre
				$meta[$pagenow]['titre'] = $default_title. ' - ' .$meta[$pagenow]['titre'];
				
				// keywords
				$meta[$pagenow]['keywords'] = NULL;
				/* $keywords = explode(' ', $meta[$pagenow]['description']);
				shuffle($keywords);
				for($i = 0; $i <= 20; $i++) $meta[$pagenow]['keywords'] .= str::slugify($keywords[$i]). ' '; */
								
				return $meta[$pagenow][$mode];
			}
			else return $default_title;
		}
	}
		
	// Temps de calcul
	function timer($event = NULL)
	{
		if(!isset($this->timer)) $this->timer['start'] = microtime(true);
		if($event) $this->timer[$event] = microtime(true) - $this->timer['start'];
		if($event == 'end' and LOCAL)
		{
			$this->timer['start'] = 0.0;
			print_r($this->timer);
		}
	}
}

/* 
########################################
########## CLASSE DISPATCH #############
########################################
*/

class dispatch extends Cerberus
{
	private $current;
	
	// Initilisation de Dispatch
	function __construct($current = NULL)
	{	
		global $pageVoulue;
		global $sousPageVoulue;
		
		// Page en cours
		if(!empty($current)) $this->current = $current;
		else
		{
			if($sousPageVoulue == 'admin' and isset($_GET['admin'])) $this->current = $pageVoulue. '-' .get('admin');
			else $this->current = $pageVoulue. '-' .$sousPageVoulue;
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
			$value = a::beArray($value);
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
		if(isset($modules[$this->current])) $arrayModules = a::beArray($modules[$this->current]);
		if(isset($modules[$this->global])) $arraySubmodules = a::beArray($modules[$this->global]);
		if(isset($modules['*'])) $arrayGlobal = a::beArray($modules['*']);
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
		if($modules) new Cerberus($modules, 'include');
	}
	
	// Modules JS/CSS
	function getAPI($scripts)
	{
		global $switcher;
		
		// API
		$availableAPI = array(
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.7.0/jquery.min.js',
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'colorbox' => 'jquery.colorbox-min',
		'nivoslider' => 'jquery.nivo.slider.pack');
		
		if(isset($switcher)) $path = $switcher->path();
		$defaultCSS = 'css/styles.css';
		$defaultJS = 'js/core.js';
		$this->JS = $this->CSS = array();
		
		// Fichiers par défaut
		a::beArray($scripts['*']);
		$scripts['*'][] = 'assets/css/cerberus.css';
		$scripts['*'][] = 'assets/'.$defaultCSS;
		$scripts['*'][] = 'assets/'.$defaultJS;
		
		// Fichiers spécifiques aux pages
		a::beArray($scripts[$this->current]);
		a::beArray($scripts[$this->global]);
		
		$scripts[$this->current][] = $this->current;
		$scripts[$this->global][] = $this->global;
		
		// Fichiers switch
		if(isset($path))
		{
			$scripts['*'][] = $path.$defaultCSS;
			$scripts['*'][] = $path.$defaultJS;
		}
		
		$scripts = $this->dispatchArray($scripts);
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
	
	// Affichage des scripts
	function getCSS()
	{
		$this->CSS['url'][] = 'min/?f=' .implode(',', array_filter($this->CSS['min']));
		echo '<link rel="stylesheet" type="text/css" href="' .implode('" /><link rel="stylesheet" type="text/css" href="', $this->CSS['url']). '" />' . "\n";		
		if(isset($this->CSS['inline'])) echo '<style type="text/css">' .implode("\n", $this->CSS['inline']). '</style>' . "\n";
	}
	function getJS()
	{
		$this->JS['url'][] = 'min/?f=' .implode(',', array_filter($this->JS['min']));
		echo '<script type="text/javascript" src="' .implode('"></script>' . "\n". '<script type="text/javascript" src="', $this->JS['url']). '"></script>' . "\n";		
		if(isset($this->JS['inline'])) echo '<script type="text/javascript">' .implode("\n", $this->JS['inline']). '</script>' . "\n";
	}
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