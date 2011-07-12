<?php
session_start();
include('tools/sfputs.php');
include('tools/display.php');

function cerberusDispatch($array, $page)
{
	if(!empty($page) and isset($array[$page])) $cerberus = new Cerberus($array[$page], 'include');
}

class Cerberus
{
	/* ########################################
	############### PREPARATION ############
	######################################## */

	// Paramètres
	private $render;
	private $erreur;
	private $mode = 'core';
	
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}	
			
	function __construct($modules, $mode = 'core', $reset = true)
	{
		// Préparation des variables
		if($mode != 'core')
		{
			if(isset($_GET['page'])) $this->mode = $_GET['page'];
			else $this->mode = $mode;
		}

		// Création ou non du fichier
		if(!file_exists('cerberus/cache/' .$this->mode. '.php')) $reset = TRUE;
		if($reset == TRUE)
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
			'[admin]' => array('Admin', 'normalize', 'is_blank', 'getLastID'),
			'[mail]' => array('Mail', 'postVar', 'stripHTML'),
			'[form]' => array('Form', 'normalize'),
			'[check]' => array('checkMail', 'checkPhone'));
		
			foreach($modules as $value)
			{
				if(strpos($value, '[') !== FALSE and isset($packages[$value])) foreach($packages[$value] as $includePack) $modulesArray[] = $includePack;
				else $modulesArray[] = $value;
			}
			
			$modulesArray = array_unique($modulesArray);
			asort($modulesArray);
			foreach($modulesArray as $value) $this->loadModule($value);
		}
	}
	
	function loadModule($module)
	{
		// Récupération des données
		if(file_exists('cerberus/tools/' .$module. '.php')) $thisModule = $this->file_get_contents_utf8('cerberus/tools/' .$module. '.php');
		elseif(file_exists('cerberus/class/class' .$module. '.php')) $thisModule = $this->file_get_contents_utf8('cerberus/class/class' .$module. '.php');
		else $this->erreurs[] = 'Module' .$module. ' non existant.';
		
		// Traitement de la fonction obtenue
		if(isset($thisModule))
		{
			$thisModule = trim($thisModule);
			$thisModule = substr($thisModule, 5, -2);
			$this->render .= $thisModule;
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