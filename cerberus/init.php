<?php
/**
 * init.php
 * Initiates a basic Cerberus page
 */

// Including bootstrap file
if(!class_exists('Init'))
	require 'class/core.init.php';

// Creating main object
if(!isset($init)) $init = null;
$init = new Init(null, $init);

// Build pages
if(LOCAL and isset($_GET['cerberus_build']))
{
	if(!class_exists('Build')) require 'cerberus/class/class.build.php';
	$build = new Build();
}

content::start();

	/**
	 * Loading config file
	 * Setting main constants
	 * Load dispatch
	 * Connecting to a database
	 * Update core
	 * Log user stats
	 */
	$init->startup('dispatch mysql update stats');

	// Setting cache manifest if existing
	$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest'))
		? ' manifest="cache.manifest"' : null;

	// Adding browser sniffing (I know) to the html tag
	$uaSniff = config::get('uasniff')
		? ' class="' .browser::css(). '"' : null;

	echo '<!DOCTYPE html>'.PHP_EOL;
	echo '<html' .$manifest.$uaSniff. '>'.PHP_EOL;

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