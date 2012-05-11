<?php
function __class_loader($class_name) 
{
	$class_name = str_replace('_', '.', strtolower($class_name));
	$file = glob(PATH_MAIN.'cerberus/class/{kirby,class,core,kirby.plugins}.' .$class_name. '*.php', GLOB_BRACE);
	if($file and file_exists($file[0]) and !class_exists($class_name))
	{
		require_once($file[0]); 
		if(method_exists($class_name, 'init')) 
			call_user_func(array($class_name, 'init')); 
		return true;
	}
}
?>