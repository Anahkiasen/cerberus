<?php
// Including bootstrap file
include('class/core.init.php');
$cerberus = new init();

content::start();

	// Loading config file
	$cerberus->config();
	
	// Setting main constants
	$cerberus->constants();
	
	// Load dispatch
	$cerberus->dispatch();
	
	// Connecting to database
	$cerberus->mysql();
	
	// Mise à jour du moteur
	$cerberus->update();
	
	// Log user stats
	$cerberus->stats();
	
	// Setting cache manifest if existing
	$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest')) ? 'manifest="cache.manifest"' : NULL;
	
	// Adding browser sniffing (I know) to the html tag
	echo '<!DOCTYPE html>'.PHP_EOL;
	echo '<html ' .$manifest. ' class="' .browser::css(). '">'.PHP_EOL;
	
	content::start();
	
		// Create required files and folders
		$cerberus->required();
		
		// Loading translations index
		$cerberus->language();
		
		// Loading navigation structure
		$cerberus->navigation();
		
		// Display debug informations 
		$cerberus->debug();
		
		// Cache the page
		$cerberus->cache();
		
		// Loading cerberus modules
		$cerberus->modules();
		
		// Backing up database
		$cerberus->backup();
		
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