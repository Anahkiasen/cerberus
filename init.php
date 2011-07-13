<?php
session_start();
include('tools/sfputs.php');
include('tools/display.php');
include('tools/getURL.php');

class Cerberus
{
	/* ########################################
	############### PREPARATION ############
	######################################## */

	// Paramètres
	private $render;
	private $erreur;
	private $mode = 'core';
	private $resetMode = TRUE;
	
	// URL de la page principale
	public $url = 'index.php';
	
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', TRUE));
	}	
			
	function __construct($modules, $mode = 'core', $reset = TRUE)
	{
		$this->resetMode = $reset;
		$this->url = getURL(TRUE);
	
		// Mode de Cerberus (core/include)
		if($mode != 'core')
		{
			$this->mode = (isset($_GET['page']))
				? $_GET['page']
				: $mode;
		}

		// Création ou non du fichier
		if(!file_exists('cerberus/cache/' .$this->mode. '.php')) $this->resetMode = TRUE;
		if($this->resetMode == TRUE)
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
	
	function loadCerberus($modules = '')
	{				
		// Chargement des modules
		if(!empty($modules))
		{
			if(!is_array($modules)) $modules = array($modules);
			
			// Packs
			$packages = array(
			'[sql]' => array('connectSQL', 'mysqlQuery', 'html', 'bdd'),
			'[admin]' => array('Admin', 'findString', 'getLastID', 'getURL', 'is_blank', 'normalize'),
			'[mail]' => array('Mail', 'postVar', 'stripHTML'),
			'[form]' => array('Form', 'normalize'),
			'[check]' => array('checkMail', 'checkPhone'));
		
			foreach($modules as $value)
			{
				if(strpos($value, '[') !== FALSE and isset($packages[$value])) foreach($packages[$value] as $includePack) $modulesArray[] = $includePack;
				else $modulesArray[] = $value;
			}
			
			// Nettoyage de l'array des fonctions et mise en cache du core ; chargement des modules
			$modulesArray = array_unique($modulesArray);
			asort($modulesArray);
						
			if($this->mode == 'core') $this->cacheCore = $modulesArray; 
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
		elseif(file_exists('cerberus/class/class' .$module. '.php'))  $thisModule = $this->file_get_contents_utf8('cerberus/class/class' .$module. '.php');
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
			
			if(!empty($newModules)) $cerberus = new Cerberus($newModules, 'include', $this->resetMode);
		}
	}
	
	/* ########################################
	########### RENDU DU FICHIER #############
	######################################## */
	
	function generate()
	{
		// Affichage des erreurs et création du fichier
		if(!empty($this->erreurs)) foreach($this->erreurs as $value) echo $value. '<br />';
		else sfputs('cerberus/cache/' .$this->mode. '.php', '<?php' .$this->render. '?>');
	}
	
	function inclure()
	{
		if(file_exists('cerberus/cache/' .$this->mode. '.php')) include_once('cerberus/cache/' .$this->mode. '.php');
		else echo 'Fichier ' .$this->mode. ' non trouvé';
	}
}
?>