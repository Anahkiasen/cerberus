<?php
// Including bootstrap file
include('class/core.init.php');
$init = new init();

content::start();

	// Loading config file
	$init->config();
	
	// Setting main constants
	$init->constants();
	
	// Load dispatch
	$init->dispatch();
	
	// Connecting to database
	$init->mysql();
	
	// Mise à jour du moteur
	$init->update();
	
	// Log user stats
	$init->stats();
	
	// Setting cache manifest if existing
	$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest')) ? 'manifest="cache.manifest"' : NULL;
	
	// Adding browser sniffing (I know) to the html tag
	echo '<!DOCTYPE html>'.PHP_EOL;
	echo '<html ' .$manifest. ' class="' .browser::css(). '">'.PHP_EOL;
	
	content::start();
	
		// Create required files and folders
		$init->required();
		
		// Loading translations index
		$init->language();
		
		// Loading navigation structure
		$init->navigation();
		
		// Display debug informations 
		$init->debug();
		
		// Cache the page
		$init->cache();
		
		// Backing up database
		$init->backup();
		
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
?>