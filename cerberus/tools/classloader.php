<?php
/**
 * Loads a class if called
 *
 * @param string    $class_name The class to load
 */
function __class_loader($class_name)
{
	// Replaces all _ with . to match Cerberus naming convention
	$class_name = str_replace('_', '.', strtolower($class_name));

	// List all the matching classes
	$file = glob(PATH_CORE.'class/{kirby,class,core,kirby.plugins}.' .$class_name. '*.php', GLOB_BRACE);

	// If we found correspond classes and they're not already loaded
	if($file and file_exists($file[0]) and !class_exists($class_name))
	{
		require_once($file[0]);

		// Don't remember this but it must have been important... I guess
		if(method_exists($class_name, 'init'))
			call_user_func(array($class_name, 'init'));
	}
}
?>
