<?php
session_start();
include('tools/beArray.php');

class Cerberus
{
	/*
	########################################
	############### PREPARATION ############
	########################################
	*/
	
	public $productionMode;
	public $meta;

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
		$modules = array_merge(array(
			'beArray', 'display', 'boolprint', 'timthumb',
			'findString', 'sfputs', 'simplode', 'sunlink'),
			beArray($modules));
	
		// Mode production
		if(!defined('PRODUCTION')) $this->defineProduction();
	
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
		
		// Connexion à la base de données
		if(file_exists('cerberus/cache/conf.php')) connectSQL();
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
			'php/');
		
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
		if(!function_exists($module))
		{
			$fichierModule = $this->getFile($module);
			if($fichierModule)
			{
				$thisModule = trim($this->file_get_contents_utf8($fichierModule));
				$thisModule = substr($thisModule, 5, -2);
				$this->render .= $thisModule;
			}
			else $this->erreurs[] = 'Module ' .$module. ' non existant.';
		}
	}
	
	// Répartition des fonctions entre les pages
	function cerberusDispatch($array, $page = NULL, $mode = 'PHP')
	{		
		// API
		$availableAPI = array(
		'jQuery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js',
		'jQueryUI' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'ColorBox' => 'jquery.colorbox-min',
		'nivoSlider' => 'jquery.nivo.slider.pack');
	
		// Page en cours
		global $pageVoulue;
		global $sousPageVoulue;
		
		// Dédoublage des groupes
		foreach($array as $key => $value)
		{
			if(strpos($key, ',') != FALSE)
			{
				$keys = explode(',', $key);
				foreach($keys as $pages)
				{
					$array[$pages] = (isset($array[$pages]))
					? array_merge($value, $array[$pages])
					: $value;
				}
				unset($array[$key]);
			}
		}		
		
		// Variables par défaut
		if(empty($page)) $page = $pageVoulue. '-' .$sousPageVoulue;
		$explode = explode('-', $page); 
		$css = $js = "\n";
		$thisModules =
		$thisSubmodules = array();
		
		// Véritifcation de la présence de modules concernés (page/pagesouspage/*)
		if(isset($array[$page])) $thisModules = beArray($array[$page]);
		if(isset($array[$explode[0]])) $thisSubmodules = beArray($array[$explode[0]]);
		if(isset($array['*'])) 
		{
			$array['*'] = beArray($array['*']);	
			$thisModules = array_merge($array['*'], $thisModules);
		}
		$renderArray = array_merge($thisModules, $thisSubmodules);
		if(isset($this->cacheCore) and $mode == 'PHP') $renderArray = array_values(array_diff($renderArray, $this->cacheCore));
				
		if(!empty($renderArray))
		{
			// Suppressions des fonctions non voulues
			foreach($renderArray as $key => $value)
			{
				if(findString('!', $value))
				{
					unset($renderArray[array_search(substr($value, 1), $renderArray)]);
					unset($renderArray[$key]);
				}
			}

			// Traitement des modules
			if($mode == 'API')
			{
				foreach($renderArray as $value) 
				{
					$thisScript = strtolower($value);
					if(isset($availableAPI[$value]))
					{
						if(file_exists('css/' .$thisScript. '.css')) $minCSS[] = 'css/' .$thisScript. '.css';
						if(findString('http', $availableAPI[$value])) $js .= '<script type="text/javascript" src="' .$availableAPI[$value]. '"></script>';
						else $minJS[] = 'js/' .$availableAPI[$value]. '.js';
					}
					elseif(findString('.js', $thisScript)) $minJS[] = $thisScript;
					elseif(findString('.css', $thisScript)) $minCSS[] = $thisScript;
					else
					{
						if(file_exists('js/' .$thisScript. '.js')) $minJS[] = 'js/' .$thisScript. '.js';
						if(file_exists('css/' .$thisScript. '.css')) $minCSS[] = 'css/' .$thisScript. '.css';
					}
				}
				
				if(!empty($minCSS)) $css = '<link type="text/css" rel="stylesheet" href="min/?f=' .implode(',', $minCSS). '" />';
				if(!empty($minJS)) $js .= '<script type="text/javascript" src="min/?f=' .implode(',', $minJS). '"></script>';
				return array(trim($css), trim($js), $renderArray);
			}
			else $cerberus = new Cerberus($renderArray, 'include');
		}
	}
	
	// Répartition des scripts et styles
	function cerberusAPI($array, $page = NULL)
	{
		global $pageVoulue;
		global $sousPageVoulue;
		global $switcher;
		
		if(isset($switcher)) $path = $switcher->path();
		
		// Fichiers par défaut
		$array['*'] = beArray($array['*']);
		$defaultCSS = 'css/styles.css';
		$defaultJS = 'js/scripts.js';
		$array['*'][] = 'css/cerberus.css';
		if(file_exists($defaultCSS)) $array['*'][] = $defaultCSS;
		if(file_exists($defaultJS)) $array['*'][] = $defaultJS;
		if(isset($path))
		{
			if(file_exists($path.$defaultCSS)) $array['*'][] = $path.$defaultCSS;
			if(file_exists($path.$defaultJS)) $array['*'][] = $path.$defaultJS;
		}
				
		// Fichiers spécifiques aux pages
		$precore = array($pageVoulue, $pageVoulue. '-' .$sousPageVoulue);
		foreach($precore as $thispage)
		{
			$css = 'css/page-' .$thispage. '.css';
			$js = 'js/page-' .$thispage. '.js';
			
			if(file_exists($css)) $array[$thispage][] = $css;
			if(file_exists($js)) $array[$thispage][] = $js;
			if(isset($path))
			{
				if(file_exists($path.$css)) $array[$thispage][] = $path.$css;
				if(file_exists($path.$js)) $array[$thispage][] = $path.$js;
			}
		}
		return $this->cerberusDispatch($array, $page, 'API');
	}
		
	/* 
	########################################
	########### RENDU DU FICHIER ###########
	########################################
	*/
	
	function generate()
	{
		// Affichage des erreurs et création du fichier
		if(!empty($this->erreurs)) foreach($this->erreurs as $value) echo $value. '<br />';
		else
		{
			$thisFile = fopen('cerberus/cache/' .$this->mode. '.php', 'w+');
			fputs($thisFile, '<?php' .$this->render. '?>');
			fclose($thisFile);
		}
	}
	
	function inclure()
	{
		include_once('cerberus/cache/' .$this->mode. '.php');
	}
	
	/* 
	########################################
	########## FONCTIONS EXPORT ############
	########################################
	*/
	
	// Fonction Inject
	function injectModule()
	{
		$module = func_get_args();
		foreach($module as $thismodule)
		{
			if(!function_exists($thismodule)) include($this->getFile($thismodule));
		}
	}

	// Fonction META 
	function meta($mode = 'meta')
	{
		if($mode == 'meta') return mysqlQuery('SELECT * FROM meta ORDER BY page ASC', true, 'page');
		else
		{
			global $meta;
			global $pageVoulue;
			global $sousPageVoulue;
		
			$defaultTitle = index('menu-' .$pageVoulue);
			if(isset($meta[$pageVoulue. '-' .$sousPageVoulue]))
			{
				$thisMeta = $meta[$pageVoulue. '-' .$sousPageVoulue];
				$thisMeta['titre'] = $defaultTitle. ' - ' .$thisMeta['titre'];
				return $thisMeta[$mode];
			}
			else if($mode == 'titre') return $defaultTitle;
		}
	}
	// Mode production ou non
	function defineProduction()
	{
		global $PRODUCTION;
		global $REWRITING;
		
		if(!isset($PRODUCTION)) $PRODUCTION = TRUE;
		if(!isset($REWRITING)) $REWRITING = TRUE; 
		
		if($this->isLocal())
		{
			define('PRODUCTION', FALSE);
			define('REWRITING', FALSE);
		}
		else
		{
			define('PRODUCTION', $PRODUCTION);
			define('REWRITING', $REWRITING);
		}
	}
	
	// En local ou non
	function isLocal()
	{
		return in_array($_SERVER['HTTP_HOST'], array('localhost:8888', '127.0.0.1'));
	}
}
?>