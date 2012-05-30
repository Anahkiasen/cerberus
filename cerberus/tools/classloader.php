<?php
/**
 * Loads a class if called
 *
 * @param string    $className The class to load
 */
function __autoLoader($className)
{
	// Replaces all _ with . to match Cerberus naming convention
	$className = str_replace('_', '.', strtolower($className));

	// List all the matching classes
	$file = glob(PATH_CORE.'class/{kirby,class,core,kirby.plugins}.' .$className. '*.php', GLOB_BRACE);

	// If we found correspond classes and they're not already loaded
	if($file and file_exists($file[0]) and !class_exists($className))
	{
		require_once($file[0]);

		// Don't remember this but it must have been important... I guess
		if(method_exists($className, 'init'))
			call_user_func(array($className, 'init'));
	}
}
