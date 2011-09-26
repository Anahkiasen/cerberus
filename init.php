<?php
session_start();
include('tools/beArray.php');

class Cerberus
{
	/* ######################################
	############### PREPARATION ############
	######################################## */
	
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
			
	function __construct($modules, $productionMode = FALSE, $mode = 'core')
	{
		$modules = beArray($modules);		
		
		// Modules coeur
		$modules = array_merge(
			array(
				'beArray', 'display', 'boolprint', 'timthumb',
				'findString', 'sfputs', 'simplode', 'sunlink'),
			$modules);
		$this->productionMode = $productionMode;
	
		// Mode de Cerberus (core/include)	
		if($mode != 'core' and isset($_GET['page']) and !empty($_GET['page'])) $this->mode = $_GET['page'];
		elseif($mode != 'core' and !isset($_GET['page'])) $this->mode = 'home';
		else $this->mode = 'core';

		// Création ou non du fichier
		if($this->productionMode == FALSE or !file_exists('cerberus/cache/' .$this->mode. '.php'))
		{
			$this->loadCerberus($modules);
			$this->generate();
		}
		
		// Include du fichier
		$this->inclure();
		
		if(file_exists('cerberus/cache/conf.php')) connectSQL();

		// Définition des constantes
		if(!defined('PRODUCTION'))
			define('PRODUCTION', FALSE);
	}
	
	/* ########################################
	###### RECUPERATION DES FONCTIONS #########
	######################################## */
	
	function loadCerberus($modules)
	{				
		// Chargement des modules
		if(!empty($modules))
		{
			$modules = beArray($modules);
			
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL', 'connectSQL', 'mysqlQuery', 'bdd'),
			'pack.navigation' => array('normalize', 'desiredPage', 'getURL', 'rewrite'),
			'pack.rewrite' => array('baseref', 'rewrite', 'normalize'),
			'class.admin' => array('admin', 'findString', 'getURL', 'normalize', 'randomString'),
			'class.desired' => array('desiredPage', 'getURL'),
			'class.mail' => array('mail', 'findString', 'stripHTML'),
			'class.form' => array('form', 'checkString', 'normalize'),
			'class.news' => array('news', 'bbcode', 'getURL', 'truncate'));
		
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
	
	function loadModule($module)
	{
		// Modules customs
		$path = (strpos($module, 'php/') === FALSE)
		? 'cerberus'
		: 'php';
		if($path == 'php') $module = substr($module, 4);
		
		// Récupération des données
		if(file_exists($path. '/tools/' .$module. '.php'))
		{
			if(!function_exists($module))
				$thisModule = $this->file_get_contents_utf8($path. '/tools/' .$module. '.php');
		}
		elseif(file_exists($path. '/class/' .$module. '.class.php')) $thisModule = $this->file_get_contents_utf8($path. '/class/' .$module. '.class.php');
		else $this->erreurs[] = 'Module ' .$module. ' non existant.';
		
		// Traitement de la fonction obtenue
		if(isset($thisModule))
		{
			$thisModule = trim($thisModule);
			$thisModule = substr($thisModule, 5, -2);
			$this->render .= $thisModule;
		}
	}
	
	// Répartition des fonctions entre les pages
	function cerberusDispatch($array, $page = '', $mode = 'PHP')
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
			else $cerberus = new Cerberus($renderArray, $this->productionMode, 'include');
		}
	}
	
	// Répartition des scripts et styles
	function cerberusAPI($array, $page = '')
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
		
	/* ########################################
	########### RENDU DU FICHIER #############
	######################################## */
	
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
	
	/* ########################################
	########### FONCTIONS EXPORT #############
	######################################## */

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
	function debugMode()
	{
		if($_SERVER['HTTP_HOST'] == 'localhost:8888') return false;
		else return $this->productionMode;	
	}
	
	// En local ou non
	function isLocal()
	{
		return in_array($_SERVER['HTTP_HOST'], array('localhost:8888', '127.0.0.1'));
	}
}
?>