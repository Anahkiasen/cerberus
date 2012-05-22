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
		$this->mode = $mode;
		
		// Création ou non du fichier
		if(!file_exists(PATH_CACHE.$this->mode. '.php'))
		{
			$this->unpackModules($modules);
			$this->generate();
		}
		
		// Include du fichier
		f::inclure(PATH_CACHE .$this->mode. '.php');
	}	
	
	/* 
	########################################
	#### RECUPERATION DES FONCTIONS ########
	########################################
	*/
	
	// Chargement du moteur Cerberus
	function unpackModules($modules = '')
	{	
		$modules = a::force_array($modules);
				
		// Tri des modules et préparation des packs
		if(!empty($modules))
		{
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL'),
			'pack.navigation' => array('navigation'),
			'class.admin' => array('admin', 'admin.setup'),
			'class.mail' => array('smail'),
			'class.form' => array('form', 'checkFields'),
			'class.news' => array('news', 'bbcode'));
		
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
			PATH_CORE.'tools/',
			PATH_CORE.'class/',
			PATH_COMMON.'php/');
		
		foreach($cheminsValides as $chemin)
		{
			return f::path(
				$chemin.$module.'.php',
				$chemin.'class.'.$module.'.php',
				$chemin.'svn.'.$module.'.php');
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
				if(CACHE)
				{
					$thisModule = trim($this->file_get_contents_utf8($fichierModule));
					$thisModule = substr($thisModule, 5, -2);
					$this->render .= $thisModule;
				}
				else f::inclure($fichierModule);
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
			if(!function_exists($thismodule) and !class_exists($thismodule) and !class_exists(substr($thismodule, 6)))
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
				f::write(PATH_CACHE .$this->mode. '.php', '<?php' .$this->render. '?>');
	}
}
?>