<?php
session_start();

function loadModule($module)
{
	if(file_exists('cerberus/tools/' .$module. '.php')) include_once('cerberus/tools/' .$module. '.php');
	else $erreurs[] = 'Module ' .$module . ' non existant';
}
function loadClass($class)
{
	if(file_exists('cerberus/class/class' .$class. '.php')) include_once('cerberus/class/class' .$class. '.php');
	else $erreurs[] = 'Classe ' .$class . ' non existant';
}

function cerberus($modules = '')
{
	// Packs
	$packages = array(
	'[SQL]' => array('connectSQL', 'mysqlQuery', 'html', 'bdd')	);
	
	// Chargement des modules
	if(!empty($modules))
	{
		if(is_array($modules)) foreach($modules as $value)
		{
			if(strpos($value, '[') !== FALSE and isset($packages[$value])) foreach($packages[$value] as $includePack) loadModule($includePack);
			else loadModule($value);
		}
		else loadModule($modules);
	}
	
	// Rapport d'erreur
	if(!empty($erreurs)) foreach($erreurs as $value) echo $value. '<br />';
}
?>