<?php
session_start();
include('tools/beArray.php');
include('tools/errorHandle.php');

class Cerberus
{
	/*
	########################################
	############### PREPARATION ############
	########################################
	*/
	
	// Cache des modules coeur
	protected $cacheCore;

	// Paramètres
	private $render;
	private $erreur;
	
	// Modes
	private $mode;
	private $serverLocal = TRUE;
		
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', TRUE));
	}	
	
	function __construct($modules, $mode = 'core')
	{
		// Environnement du site
		if(!defined('PRODUCTION'))
		{
			include('cerberus/conf.php');

			if(!isset($PRODUCTION)) $PRODUCTION = TRUE;
			if(!isset($REWRITING)) $REWRITING = TRUE; 
			
			define('MULTILANGUE', isset($LANGUES));
			if(MULTILANGUE) $this->langues = $LANGUES;
			
			if(in_array($_SERVER['HTTP_HOST'], array('localhost:8888', '127.0.0.1')))
			{
				define('PRODUCTION', FALSE);
				define('REWRITING', FALSE);
				define('LOCAL', TRUE);
			}
			else
			{
				define('PRODUCTION', $PRODUCTION);
				define('REWRITING', $REWRITING);
				define('LOCAL', FALSE);
			}
		}
		
		// Affichage et gestion des erreurs
		error_reporting(E_ALL|E_STRICT);
		set_error_handler('errorHandle');

		// Modules coeur
		$modules = beArray($modules);
		if($mode == 'core') $modules = array_merge(array(
			'errorHandle',
			'beArray', 'display', 'boolprint', 'timthumb',
			'findString', 'sexist', 'sfputs', 'simplode', 'sunlink'),
			$modules);
		
		// Mode de Cerberus (core/include)	
		if($mode != 'core' and isset($_GET['page']) and !empty($_GET['page'])) $this->mode = $_GET['page'];
		elseif($mode != 'core' and !isset($_GET['page'])) $this->mode = 'home';
		else $this->mode = 'core';

		// Création ou non du fichier
		if(!PRODUCTION or !file_exists('cerberus/cache/' .$this->mode. '.php'))
		{
			$this->unpackModules($modules);
			$this->generate();
		}
				
		// Include du fichier
		$this->inclure();
	
		// Lancement des modules annexes
		if($this->mode == 'core')
		{
			global $index;
			global $userAgent;
			
			if(file_exists('cerberus/conf.php'))
			{
				connectSQL();
				$this->meta();
				if(MULTILANGUE) $index = createIndex($this->langues);
			}
			if(in_array('browserSelector', $modules))
				if(function_exists('browserSelector')) browserSelector($userAgent);
		}
	}
		
	
	/* 
	########################################
	#### RECUPERATION DES FONCTIONS ########
	########################################
	*/
	
	// Chargement du moteur Cerberus
	function unpackModules($modules)
	{				
		// Tri des modules et préparation des packs
		if(!empty($modules))
		{
			$modules = beArray($modules);
		
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL', 'connectSQL', 'mysqlQuery', 'bdd'),
			'pack.navigation' => array('baseref', 'desiredPage', 'rewrite'),
			'class.admin' => array('admin', 'admin.setup', 'getURL', 'randomString'),
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
			
			foreach($modulesArray as $value) $this->loadModule($value);
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
	
	// Include du fichier voulu
	function inclure()
	{
		if(file_exists('cerberus/cache/' .$this->mode. '.php')) include_once('cerberus/cache/' .$this->mode. '.php');
	}
	
	/* 
	########################################
	########## FONCTIONS EXPORT ############
	########################################
	*/
	
	// Fonction META 
	function meta($mode = 'meta')
	{
		global $meta;
		
		if($mode == 'meta') $meta = mysqlQuery('SELECT * FROM meta ORDER BY page ASC', true, 'page');
		else
		{
			global $pageVoulue;
			global $sousPageVoulue;
		
			$defaultTitle = index('menu-' .$pageVoulue);
			if($pageVoulue == 'admin' and isset($_GET['admin'])) $defaultTitle = 'Gestion ' .ucfirst($_GET['admin']);
			if(isset($meta[$pageVoulue. '-' .$sousPageVoulue]))
			{
				$thisMeta = $meta[$pageVoulue. '-' .$sousPageVoulue];
				$thisMeta['titre'] = $defaultTitle. ' - ' .$thisMeta['titre'];
				return $thisMeta[$mode];
			}
			else if($mode == 'titre') return $defaultTitle;
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
			$value = beArray($value);
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
		if(isset($modules[$this->current])) $arrayModules = beArray($modules[$this->current]);
		if(isset($modules[$this->global])) $arraySubmodules = beArray($modules[$this->global]);
		if(isset($modules['*'])) $arrayGlobal = beArray($modules['*']);
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
		beArray($scripts['*']);
		$scripts['*'][] = 'assets/css/cerberus.css';
		$scripts['*'][] = 'assets/'.$defaultCSS;
		$scripts['*'][] = 'assets/'.$defaultJS;
		
		// Fichiers spécifiques aux pages
		beArray($scripts[$this->current]);
		beArray($scripts[$this->global]);
		
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
			return array(trim($css), trim($js), $scripts);
		}
	}
}
?>