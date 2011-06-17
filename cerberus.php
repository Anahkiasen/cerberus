<?php
session_start();

class Cerberus
{
	private $erreurs;

	function __construct($array = '')
	{
		// Chargement des modules
		if(!empty($array))
		{
			if(is_array($array)) foreach($array as $value)
			{
				if(file_exists('tools/' .$value. '.php')) include('tools/' .$value. '.php');
				else $erreurs[] = 'Module ' .$value . ' non existant';
			}
			else $erreurs[] = 'Demande de modules invalide';
		}
		if(!empty($this->erreurs))
		{
			foreach($this->erreurs as $value) echo $value. '<br />';
		} 
	}
	function loadClass($class)
	{
		include('class/' .$class. '.php');
	}
	function loadModule($module)
	{
		include('tools/' .$module. '.php');
	}
}
?>