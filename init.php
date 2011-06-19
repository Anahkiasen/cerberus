<?php
session_start();
include('tools/sfputs.php');

class Cerberus
{
	private $render;
	private $erreur;
		
	function __construct($modules = '')
	{
		// Packs
		$packages = array(
		'[SQL]' => array('connectSQL', 'mysqlQuery', 'html', 'bdd'));
		
		// Chargement des modules
		if(!empty($modules))
		{
			if(is_array($modules)) foreach($modules as $value)
			{
				if(strpos($value, '[') !== FALSE and isset($packages[$value])) foreach($packages[$value] as $includePack) $this->loadModule($includePack);
				else $this->loadModule($value);
			}
			else $this->loadModule($modules);
		}
		
		// Rapport d'erreur
		if(!empty($this->erreur)) foreach($this->erreur as $value) echo $value. '<br />';
		else if(file_exists('cerberus/cerberus.php')) sfputs('cerberus/cerberus.php', $this->render);
		
		include_once('cerberus.php');
	}
	
	function loadModule($module)
	{
		if(file_exists('cerberus/tools/' .$module. '.php')) $this->render = file_get_contents('cerberus/tools/' .$module. '.php');
		elseif(file_exists('cerberus/class/class' .$module. '.php')) $this->render = file_get_contents('cerberus/class/class' .$module. '.php');
		else $this->erreurs[] = 'Module' .$module. ' non existant.';
	}
}
?>