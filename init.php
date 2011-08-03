<?php
session_start();

class Cerberus
{
	/* ######################################
	############### PREPARATION ############
	######################################## */

	// Paramètres
	private $render;
	private $erreur;
	
	// Modes
	private $mode;
	private $resetMode;
		
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', TRUE));
	}	
			
	function __construct($modules, $resetMode = TRUE, $mode = 'core')
	{
		// Modules coeur
		$modules = array_merge(array('sfputs', 'simplode', 'getURL', 'display', 'boolprint', 'timthumb'), $modules);
		$this->resetMode = $resetMode;
	
		// Mode de Cerberus (core/include)	
		if($mode != 'core' and isset($_GET['page']) and !empty($_GET['page'])) $this->mode = $_GET['page'];
		elseif($mode != 'core' and !isset($_GET['page'])) $this->mode = 'home';
		else $this->mode = 'core';

		// Création ou non du fichier
		if($this->resetMode == TRUE or !file_exists('cerberus/cache/' .$this->mode. '.php'))
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
			'[sql]' => array('connectSQL', 'mysqlQuery', 'bdd'),
			'classAdmin' => array('Admin', 'findString', 'getURL', 'normalize', 'randomString'),
			'classMail' => array('Mail', 'stripHTML'),
			'classForm' => array('Form', 'normalize'));
		
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
		elseif(file_exists('cerberus/class/class' .$module. '.php')) $thisModule = $this->file_get_contents_utf8('cerberus/class/class' .$module. '.php');
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
	function cerberusDispatch($array, $page)
	{
		if(!empty($page) and isset($array[$page])) 
		{
			if(!is_array($array[$page])) $array[$page] = array($array[$page]);
			
			if(isset($this->cacheCore)) $newModules = array_values(array_diff($array[$page], $this->cacheCore));
			else $newModules = $array[$page];
			
			if(!empty($newModules)) $cerberus = new Cerberus($newModules, $this->resetMode, 'include');
		}
	}
	
	// Fonctions API
	function cerberusAPI($array, $page)
	{
		$availableAPI = array(
		'jQuery' => 'https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js',
		'jQueryUI' => 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.13/jquery-ui.min.js',
		'swfobject' => 'https://ajax.googleapis.com/ajax/libs/swfobject/2.2/swfobject.js',
		'ColorBox' => 'js/jquery.colorbox-min.js',
		'nivoSlider' => 'js/jquery.nivo.slider.pack.js');
	
		if(isset($array[$page])) 
		{
			if(!is_array($array[$page])) $array[$page] = array($array[$page]);
		}
		else $array[$page] = array();
		
		
		// Rendu général
		$css = $js = "\n";
		if(isset($array['*'])) 
		{
			if(!is_array($array['*'])) $array['*'] = array($array['*']);	
			$renderArray = array_merge($array['*'], $array[$page]);
		}
		else $renderArray = $array[$page];
		
		// Rendus
		foreach($renderArray as $value) 
		{
			$thisScript = strtolower($value);
			if(isset($availableAPI[$value])) $js .= '<script type="text/javascript" src="' .$availableAPI[$value]. '"></script>';
			else $js .= '<script type="text/javascript" src="js/' .$value. '.js"></script>';
			
			if(file_exists('css/' .$thisScript. '.css')) $css .= '<link href="css/' .$thisScript. '.css" rel="stylesheet" type="text/css" />';
			$js .= "\n";
			$css .= "\n";
		}
		
		return array($css, $js, $renderArray);
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
		return getURL(TRUE);
	}
	function debugMode()
	{
		return $this->resetMode;	
	}
}
?>