<?php
// Including bootstrap file
include('class/core.init.php');
$init = new init();

content::start();

	/**
	 * Loading config file
	 * Setting main constants
	 * Load dispatch
	 * Connecting to a database
	 * Update core
	 * Log user stats
	 */
	$init->startup('config constants dispatch mysql update stats');

	// Setting cache manifest if existing
	$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest')) ? 'manifest="cache.manifest"' : NULL;

	// Adding browser sniffing (I know) to the html tag
	echo '<!DOCTYPE html>'.PHP_EOL;
	echo '<html ' .$manifest. ' class="' .browser::css(). '">'.PHP_EOL;

	content::start();

		/**
		 * Create required files and folders
		 * Loading translations index
		 * Loading navigation structure
		 * Display debug informations
		 * Cache the page
		 * Back up database
		 */
		$init->startup('required language navigation debug cache backup');

// -------------------------------------------------- */

/**
	* Shortcut for r::get()
	*
	* @param	 mixed		$key The key to look for. Pass false or null to return the entire request array.
	* @param	 mixed		$default Optional default value, which should be returned if no element has been found
	* @return	mixed
	* @package Kirby
	*/
function get($key = false, $default = NULL)
{
	return r::get($key, $default);
}
