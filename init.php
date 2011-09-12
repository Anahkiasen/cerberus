<?php
session_start();

class Cerberus
{
	/* ######################################
	############### PREPARATION ############
	######################################## */
	
	public $productionMode;

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
		if(!is_array($modules)) $modules = array($modules);
		
		// Modules coeur
		$modules = array_merge(
			array(
				'display', 'boolprint', 'timthumb',
				'sfputs', 'simplode', 'sunlink'),
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
	}
	
	/* ########################################
	###### RECUPERATION DES FONCTIONS #########
	######################################## */
	
	function loadCerberus($modules)
	{				
		// Chargement des modules
		if(!empty($modules))
		{
			if(!is_array($modules)) $modules = array($modules);
			
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL', 'connectSQL', 'mysqlQuery', 'bdd'),
			'pack.navigation' => array('normalize', 'desiredPage', 'getURL'),
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
		// Récupération des données
		if(file_exists('cerberus/tools/' .$module. '.php'))
		{
			if(!function_exists($module))
				$thisModule = $this->file_get_contents_utf8('cerberus/tools/' .$module. '.php');
		}
		elseif(file_exists('cerberus/class/' .$module. '.class.php')) $thisModule = $this->file_get_contents_utf8('cerberus/class/' .$module. '.class.php');
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
		'jQuery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js',
		'jQueryUI' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'ColorBox' => 'js/jquery.colorbox-min.js',
		'nivoSlider' => 'js/jquery.nivo.slider.pack.js');
	
		// Page en cours
		global $pageVoulue;
		global $sousPageVoulue;
		
		if(empty($page)) $page = $pageVoulue. '-' .$sousPageVoulue;
		$explode = explode('-', $page); 
		$css = $js = "\n";
		$thisModules =
		$thisSubmodules = array();
		
		if(isset($array[$page]))
		{
			if(!is_array($array[$page])) $thisModules = array($array[$page]);
			else $thisModules = $array[$page];
		}
		if(isset($array[$explode[0]]))
		{
			if(!is_array($array[$explode[0]])) $thisSubmodules = array($array[$explode[0]]);
			else $thisSubmodules = $array[$explode[0]];
		}
		if(isset($array['*'])) 
		{
			if(!is_array($array['*'])) $array['*'] = array($array['*']);	
			$thisModules = array_merge($array['*'], $thisModules);
		}
		$renderArray = array_merge($thisModules, $thisSubmodules);
		if(isset($this->cacheCore) and $mode == 'PHP') $renderArray = array_values(array_diff($renderArray, $this->cacheCore));
				
		// Traitement des modules
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

			if($mode == 'API')
			{
				foreach($renderArray as $value) 
				{
					$thisScript = strtolower($value);
					if(isset($availableAPI[$value])) $js .= '<script type="text/javascript" src="' .$availableAPI[$value]. '"></script>';
					else $js .= '<script type="text/javascript" src="js/' .$value. '.js"></script>';
					
					if(file_exists('css/' .$thisScript. '.css')) $css .= '<link href="css/' .$thisScript. '.css" rel="stylesheet" type="text/css" />';
					$js .= "\n";
					$css .= "\n";
				}
		
				return array(trim($css), trim($js), $renderArray);
			}
			else $cerberus = new Cerberus($renderArray, $this->productionMode, 'include');
		}
	}
	
	function cerberusAPI($array, $page = '')
	{
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

	function url()
	{
		//return geturl(TRUE);
	}
	function debugMode()
	{
		if($_SERVER['HTTP_HOST'] == 'localhost:8888') return false;
		else return $this->productionMode;	
	}
}
?>