<?php
/**
 * init.php
 * Initiates a basic Cerberus page
 */

// Including bootstrap file
require 'class/core.init.php';
$init = new Init($init);

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
	$manifest = (CACHE and file_exists('cache.manifest') and config::get('cache.manifest'))
		? 'manifest="cache.manifest"' : null;

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