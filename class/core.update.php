<?php
class update
{
	// Créer des tables manquantes
	static function table($table)
	{
		db::drop($table);
		switch($table)
		{
			case 'langue':
				db::execute('CREATE TABLE IF NOT EXISTS `langue` (
				  `tag` varchar(40) NOT NULL,
				  `fr` varchar(255) NOT NULL,
				  PRIMARY KEY (`tag`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');	
				db::execute('INSERT INTO langue VALUES ("menu-home", "Accueil")');
				break;
		
			case 'admin':
				db::execute('CREATE TABLE IF NOT EXISTS `admin` (
				  `user` varchar(32) collate utf8_unicode_ci NOT NULL,
				  `password` varchar(32) collate utf8_unicode_ci NOT NULL,
				  `droits` varchar(255) collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`user`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;

			case 'logs':
				db::execute('CREATE TABLE IF NOT EXISTS `logs` (
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
			
			case 'meta':
				db::execute('CREATE TABLE IF NOT EXISTS `meta` (
				  `id` tinyint(4) NOT NULL auto_increment,
				  `page` tinyint(4) NOT NULL,
				  `titre` text collate utf8_unicode_ci NOT NULL,
				  `description` text collate utf8_unicode_ci NOT NULL,
				  `url` varchar(50) collate utf8_unicode_ci NOT NULL,
				  `langue` enum(\'fr\') collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
				
			case 'structure':
				db::execute('CREATE TABLE IF NOT EXISTS `structure` (
				  `id` tinyint(3) NOT NULL auto_increment,
				  `page` varchar(20) collate utf8_unicode_ci NOT NULL,
				  `parent` varchar(20) collate utf8_unicode_ci NOT NULL,
				  `parent_priority` tinyint(3) NOT NULL,
				  `page_priority` tinyint(3) NOT NULL,
				  `cache` enum(\'0\',\'1\') collate utf8_unicode_ci NOT NULL,
				  PRIMARY KEY  (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;');
				break;
		}
	}
}
?>