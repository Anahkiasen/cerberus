<?php
class update
{
	// The current revision number
	private static $revision;
	
	// The last revision number to date
	private static $last = '2012-05-11,fb8ba83283d115b0abb65c0a045a0048e868805a';

	/**
	 * Sets the current revision number and updates the core
	 */
	function __construct()
	{
		// Define current revision
		self::$revision = self::revision();
		
		// Apply change to the core code
		if(self::outdate('2012-05-11'))
		{
			self::codematch('([ \.\()])swf\(', '$1media::swf(');
			self::codematch('([ \.\()])timthumb\(', '$1media::timthumb(');
			self::codematch('str_replace\((.+), ?NULL,', 'str::remove($1,');
		}

		// Update revision number
		if(self::outdate()) self::update_core(self::$last);
	}
	
	//////////////////////////////////////////////////////////////
	/////////////////////////// TOOLKIT ////////////////////////// 
	//////////////////////////////////////////////////////////////	
	
	/**
	 * Returns the current revision number
	 * @return date    The date of the last update
	 */
	static function revision()
	{
		// Get local or online revision number
		$revision = LOCAL ? config::get('revision.local') : config::get('revision.online');
		$revision = explode(',', $revision);
		
		// Return only the date part 
		return a::get($revision, 0);	
	}
	
	/**
	 * Verify if the current project is outdated or not
	 * @param  date       $date The date to match against the current revision number. Defaults to last revision number
	 * @return boolean    Boolean stating if the project is outdated or not
	 */
	static function outdate($date = NULL)
	{
		// If project still uses SVN Revision, update
		if(is_numeric(self::$revision)) return true;
		
		// If current date is older than asked one, return true
		if(!$date) $date = a::get(explode(',', self::$last), 0);
		return (self::$revision < $date);
	}
	
	/**
	 * Updates the core to a particular revision number
	 * @param string    $torev The revision number to update to
	 */
	static function update_core($torev)
	{
		// Update online or local revison number
		$rev = LOCAL ? 'revision.local' : 'revision.online';
		$hardcode = config::hardcode($rev, $torev);
		
		// Display result
		if($hardcode) str::translate('update.success', NULL, 'success');
		else str::translate('update.errror', NULL, 'error');
	}

	//////////////////////////////////////////////////////////////
	/////////////////////// CORE FUNCTIONS /////////////////////// 
	//////////////////////////////////////////////////////////////

	/**
	 * Search all pages for $search and replace with $replace - both are REGEX
	 * @param  regex   $search The string to search for
	 * @param  regex   $replace What to replace it with
	 * @return string  A list of the found matches
	 */
	static function codematch($search, $replace)
	{
		$searchLine = '#' .$search. '.+\n#';
		$search = '#' .$search. '#';
		$pages = glob('{index.php,{pages,' .PATH_COMMON. 'php}/*}', GLOB_BRACE);
		a::show($pages);
		
		echo '<div class="cerberus_debug" style="width:100%"><h2>Recherche de ' .$search. '</h2>';
		
		foreach($pages as $file)
		{
			$code = f::read($file);	
			$lines = explode("\n", $code);
			$resultats = preg_grep($search, $lines);
			$count = count($resultats);
			
			if($count >= 1)
			{
				// Affichage des matches trouvés
				echo '<strong>' .$file. ' (' .$count. ' ' .str::plural($count, 'résultats', 'résultat', 'résultat'). ')</strong><br /><ul>';
				foreach($resultats as $nb => $match)
				{
					echo '<li>
						<ins>Ligne ' .($nb+1). '</ins><br/>' 
						."	". '<strong>' .htmlentities($match). '</strong><br />' 
						."	<strong>".htmlentities(preg_replace($search, $replace, $match)). '</strong></li>';
				}
				echo '</ul>';
				
				$code = preg_replace($search, $replace, $code);
				f::write($file, $code);
			}
		}
		echo '</div>';
	}

	/**
	 * Creates a missing Cerberus table into the database
	 * @param string  $table The key of the table to create
	 */
	static function table($table)
	{
		if(!SQL) return false;
		
		db::drop($table);
		switch($table)
		{
			case 'cerberus_langue':
				db::execute('CREATE TABLE IF NOT EXISTS `cerberus_langue` (
				  `tag` varchar(40) NOT NULL,
				  `fr` varchar(255) NOT NULL,
				  PRIMARY KEY (`tag`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');	
				db::execute('INSERT INTO cerberus_langue VALUES ("menu-home", "Accueil")');
				break;
		
			case 'cerberus_admin':
				db::execute('CREATE TABLE `cerberus_admin` (
				  `account` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  `user` text COLLATE utf8_unicode_ci NOT NULL,
				  `password` text COLLATE utf8_unicode_ci NOT NULL,
				  `droits` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (`account`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;

			case 'cerberus_logs':
				db::execute('CREATE TABLE IF NOT EXISTS `cerberus_logs` (
				  `id` smallint(4) NOT NULL auto_increment,
				  `ip` varchar(20) collate utf8_unicode_ci NOT NULL,
				  `date` datetime NOT NULL,
				  `platform` varchar(10) collate utf8_unicode_ci NOT NULL,
				  `browser` varchar(10) collate utf8_unicode_ci NOT NULL,
				  `version` varchar(10) collate utf8_unicode_ci NOT NULL,
				  `engine` varchar(10) collate utf8_unicode_ci NOT NULL,
				  `mobile` enum(\'0\',\'1\') collate utf8_unicode_ci NOT NULL,
				  `locale` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
				  `domaine` varchar(255) collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
			
			case 'cerberus_meta':
				db::execute('CREATE TABLE IF NOT EXISTS `cerberus_meta` (
				  `id` tinyint(4) NOT NULL auto_increment,
				  `page` tinyint(4) NOT NULL,
				  `titre` text collate utf8_unicode_ci NOT NULL,
				  `description` text collate utf8_unicode_ci NOT NULL,
				  `url` varchar(50) collate utf8_unicode_ci NOT NULL,
				  `langue` enum(\'fr\') collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
				
			case 'cerberus_structure':
				db::execute('CREATE TABLE `cerberus_structure` (
				  `id` int(11) NOT NULL AUTO_INCREMENT,
				  `page` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  `parent` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  `parent_priority` int(11) NOT NULL DEFAULT \'0\',
				  `page_priority` int(11) NOT NULL DEFAULT \'0\',
				  `cache` enum(\'0\',\'1\') COLLATE utf8_unicode_ci NOT NULL DEFAULT \'1\',
				  `hidden` enum(\'0\',\'1\') COLLATE utf8_unicode_ci NOT NULL DEFAULT \'0\',
				  `external_link` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
				
			case 'cerberus_news':
				db::execute('CREATE TABLE IF NOT EXISTS `cerberus_news` (
				  `id` smallint(4) NOT NULL AUTO_INCREMENT,
				  `date` date NOT NULL,
				  `titre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
				  `contenu` text COLLATE utf8_unicode_ci NOT NULL,
				  `langue` enum(\'fr\',\'en\') COLLATE utf8_unicode_ci NOT NULL,
				  `path` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
		}
	}
}
?>