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
		$this->mode = ($mode == 'core')
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
			$modules = array_merge(array(
				'display', 'boolprint', 'timthumb',
				'findString', 'sexist', 'simplode', 'sunlink'),
				$modules);
		}
				
		// Tri des modules et préparation des packs
		if(!empty($modules))
		{
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL', 'mysqlQuery', 'escape'),
			'pack.navigation' => array('baseref', 'desiredPage', 'rewrite'),
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
			$extension = (strpos($chemin, 'class') === FALSE)
				? '.php'
				: '.class.php';
				
			if(file_exists($chemin.$module.$extension))
			{
				$found = true;
				return $chemin.$module.$extension;
			}
		}
		if(!isset($found)) return false;
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
	
	// Mise en cache des fonctions requises
	function generate()
	{
		// Affichage des erreurs et création du fichier
		if(!empty($this->erreurs)) foreach($this->erreurs as $value) echo $value. '<br />';
		else
		{
			if(!empty($this->render))
			{
				$thisFile = fopen('cerberus/cache/' .$this->mode. '.php', 'w+');
				fputs($thisFile, '<?php' .$this->render. '?>');
				fclose($thisFile);
			}
		}
	}
		
	/* 
	########################################
	########## FONCTIONS UTILITAIRES #######
	########################################
	*/
	
	// Fonction META 
	function meta($mode = 'meta')
	{
		global $meta;
		
		if($mode == 'meta')
		{
			$meta = a::rearrange(db::select('meta', '*', array('langue' => l::current()), 'page ASC'), 'page');
		}
		else
		{
			global $pageVoulue;
			global $sousPageVoulue;
		
			$defaultTitle = l::get('menu-' .$pageVoulue);
			if($pageVoulue == 'admin' and get('admin')) $defaultTitle = 'Gestion ' .ucfirst($_GET['admin']);
			if(isset($meta[$pageVoulue. '-' .$sousPageVoulue]))
			{
				$thisMeta = $meta[$pageVoulue. '-' .$sousPageVoulue];
				$thisMeta['titre'] = $defaultTitle. ' - ' .$thisMeta['titre'];
				return $thisMeta[$mode];
			}
			else if($mode == 'titre') return $defaultTitle;
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
		else $this->current = $pageVoulue. '-' .$sousPageVoulue;
		
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
		'jquery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js',
		'jqueryui' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'colorbox' => 'jquery.colorbox-min',
		'nivoslider' => 'jquery.nivo.slider.pack');
		
		if(isset($switcher)) $path = $switcher->path();
		$defaultCSS = 'css/styles.css';
		$defaultJS = 'js/core.js';
		$js = $css = NULL;
		
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
				//$thisScript = strtolower($value);
				$thisScript = $value;

				// Si le script est présent dans les prédéfinis
				if(isset($availableAPI[$value]))
				{
					$minCSS[] = sexist('assets/css/' .$thisScript. '.css'); // CSS annexe
					if(findString('http', $availableAPI[$value])) $js .= '<script type="text/javascript" src="' .$availableAPI[$value]. '"></script>';
					else $minJS[] = sexist('assets/js/' .$availableAPI[$value]. '.js');
				}
				
				// Si le chemin est spécifié manuellement
				elseif(findString('.js', $thisScript)) $minJS[] = sexist($thisScript);
				elseif(findString('.css', $thisScript)) $minCSS[] = sexist($thisScript);
				
				// Sinon on vérifie la présence du script dans les fichiers
				else
				{
					$minJS[] = sexist('assets/js/' .$thisScript. '.js');
					$minCSS[] = sexist('assets/css/' .$thisScript. '.css');
					if(isset($path))
					{
						$minJS[] = sexist($path.'js/' .$thisScript. '.js');
						$minCSS[] = sexist($path.'css/' .$thisScript. '.css');
					}
				}
			}
			
			// Création des fichiers Minify
			$minCSS = array_filter($minCSS);
			$minJS = array_filter($minJS);
			if(!empty($minCSS)) $css .= '<link type="text/css" rel="stylesheet" href="min/?f=' .implode(',', $minCSS). '" />';
			if(!empty($minJS)) $js .= '<script type="text/javascript" src="min/?f=' .implode(',', $minJS). '"></script>';
			return array(str::trim($css), str::trim($js), $scripts);
		}	
	}
}
?>