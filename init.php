<?php
session_start();
include('tools/sfputs.php');

class Cerberus
{
	private $render;
	private $erreur;
		
	function file_get_contents_utf8($fn)
	{
		$content = file_get_contents($fn);
		return mb_convert_encoding($content, 'UTF-8',
		mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
	}	
		
	function __construct($modules = '', $reset = FALSE)
	{		
		// Regénération du fichier coeur
		if($reset == TRUE and file_exists('cerberus/cerberus.php')) unlink('cerberus/cerberus.php'); 
		
		// Chargement des modules
		if(!file_exists('cerberus/cerberus.php'))
		{
			if(!empty($modules))
			{
				asort($modules);
				
				// Packs
				$packages = array(
				'[sql]' => array('connectSQL', 'mysqlQuery', 'html', 'bdd'),
				'[admin]' => array('Admin', 'normalize', 'is_blank', 'getLastID'),
				'[mail]' => array('Mail', 'postVar', 'stripHTML'));
			
				if(is_array($modules)) foreach($modules as $value)
				{
					if(strpos($value, '[') !== FALSE and isset($packages[$value])) foreach($packages[$value] as $includePack) $this->loadModule($includePack);
					else $this->loadModule($value);
				}
				else $this->loadModule($modules);
			}
		
			// Rapport d'erreur
			if(!empty($this->erreurs)) foreach($this->erreur as $value) echo $value. '<br />';
			else sfputs('cerberus/cerberus.php', '<?php' .$this->render. '?>');
		}
		
		include_once('cerberus.php');
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
}
?>