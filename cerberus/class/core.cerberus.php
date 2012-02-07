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
		if(!file_exists('cerberus/cache/' .$this->mode. '.php'))
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
		$modules = a::force_array($modules);
		if($this->mode == 'core')
		{
			// Modules de base
			$modules = array_merge(array(
				'timthumb', 'zencode', 'swf'),
				$modules);
		}
				
		// Tri des modules et préparation des packs
		if(!empty($modules))
		{
			// Packs
			$packages = array(
			'pack.sql' => array('backupSQL'),
			'pack.navigation' => array('navigation'),
			'class.admin' => array('admin', 'admin.setup'),
			'class.mail' => array('smail'),
			'class.form' => array('form', 'checkString'),
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
			'cerberus/tools/',
			'cerberus/class/',
			'assets/php/');
		
		foreach($cheminsValides as $chemin)
		{
			if(file_exists($chemin.$module.'.php')) return $chemin.$module.'.php';
			elseif(file_exists($chemin.'class.'.$module.'.php')) return $chemin.'class.'.$module.'.php';
			elseif(file_exists($chemin.'svn.'.$module.'.php')) return $chemin.'svn.'.$module.'.php';
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
}
?>