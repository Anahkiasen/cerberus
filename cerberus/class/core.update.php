<?php
class update
{
	private static $revision;

	// Effectue des changements dans les fichiers ou sur la base
	function __construct($revision)
	{
		global $REVISION, $REVISION_LOCAL;
		self::$revision = (LOCAL) ? $REVISION_LOCAL : $REVISION;
		
		if(self::$revision < 353)
		{
			if(!in_array('account', db::fields('cerberus_admin')))
			{
				$utilisateur = db::row('cerberus_admin', '*');
				$utilisateur['account'] = 'stappler';
				self::table('cerberus_admin');
				db::insert('cerberus_admin', $utilisateur);
			}
			self::update(353);
		}
		if(self::$revision < 355)
		{
			db::execute('ALTER TABLE  `cerberus_structure` ADD  `hidden` ENUM(\'0\', \'1\') NOT NULL AFTER  `cache`');
			db::execute('ALTER TABLE  `cerberus_structure` ADD  `external_link` VARCHAR( 255 ) NOT NULL AFTER  `hidden`');
			self::update(355);
		}
		self::update(359);
	}
	
	// Met à jour le numéro de révision
	static function update($torev)
	{
		if(self::$revision < $torev)
		{
			$rev = (LOCAL) ? 'REVISION_LOCAL' : 'REVISION';
			$init = file_get_contents('cerberus/init.php');
			$init = preg_replace('/\$' .$rev. ' = [0-9]+;/', '$' .$rev. ' = ' .$torev. ';', $init);
			f::write('cerberus/init.php', $init);
			prompt('Mise à jour ' .$torev. ' effectuée');
		}
	}

	// Remplace des parties de code
	static function codematch($search, $replace)
	{
		$searchLine = '#' .$search. '.+\n#';
		$search = '#' .$search. '#';
		$pages = glob('{index.php,pages/*}', GLOB_BRACE);
		
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

	// Créer des tables manquantes
	static function table($table)
	{
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
				db::execute('CREATE TABLE IF NOT EXISTS `cerberus_structure` (
				  `id` tinyint(3) NOT NULL auto_increment,
				  `page` varchar(20) collate utf8_unicode_ci NOT NULL,
				  `parent` varchar(20) collate utf8_unicode_ci NOT NULL,
				  `parent_priority` tinyint(3) NOT NULL,
				  `page_priority` tinyint(3) NOT NULL,
				  `cache` enum(\'0\',\'1\') collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`id`)
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