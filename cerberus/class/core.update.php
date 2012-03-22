<?php
class update
{
	private static $revision;

	// Effectue des changements dans les fichiers ou sur la base
	function __construct()
	{
		self::$revision = LOCAL ? config::get('revision.local') : config::get('revision.online');
		
		if(SQL)
		{
			// Mises à jour de la base
			if(self::$revision < 353)
			{
				if(db::is_table('cerberus_admin') and !in_array('account', db::fields('cerberus_admin')))
				{
					$utilisateur = db::row('cerberus_admin', '*');
					$utilisateur['account'] = 'stappler';
					self::table('cerberus_admin');
					db::insert('cerberus_admin', $utilisateur);
				}
				self::update(353);
			}
			if(self::$revision < 355 and db::is_table('cerberus_structure'))
			{
				db::execute('ALTER TABLE  `cerberus_structure` ADD  `hidden` ENUM(\'0\', \'1\') NOT NULL AFTER  `cache`');
				db::execute('ALTER TABLE  `cerberus_structure` ADD  `external_link` VARCHAR( 255 ) NOT NULL AFTER  `hidden`');
				self::update(355);
			}
		}
		if(self::$revision < 449)
		{
			self::codematch('\$desired->([a-z]+)\(', 'navigation::$1(');
			self::codematch('\$desired->([a-z]+)', 'navigation::$1');
			self::codematch('global \$desired;', '');
			self::codematch('navigation::page', 'navigation::$page');
		}
		if(self::$revision < 450)
		{
			self::codematch('\$dispatch->([a-zA-Z]+)\(', 'dispatch::$1(');
			self::codematch('dispatch::getPHP', 'dispatch::setPHP');
			self::codematch('dispatch::getAPI', 'dispatch::assets');
			self::codematch('global \$dispatch;', '');
		}
		if(self::$revision < 515)
		{
			self::codematch('a::simple\(', 'a::simplify(');
			self::codematch('AdminPage\(', 'admin(');
			self::codematch('class getNews', 'class news');
		}
				
		self::update_core(515);
	}
	
	// Retourne le numéro de révision
	static function revision()
	{
		return self::$revision;
	}
	
	// Met à jour le numéro de révision
	static function update_core($torev)
	{
		if(self::$revision < $torev)
		{
			$rev = LOCAL ? 'revision.local' : 'revision.online';
			
			// Fichier config
			$confphp = f::read(PATH_CONF);
			if(!empty($confphp)) $confphp = trim(substr($confphp, 5, -2));
			
			$confphp = 
				(!str::find($rev, $confphp))
					? $confphp . '$config[\'' .$rev. '\'] = ' .$torev. ';'
					
					: preg_replace(
						'#\$config\[\'(' .$rev. ')\'\] = (.+);#',
						'$config[\'$1\'] = ' .$torev. ';',
						$confphp);
			$confphp = '<?php' .PHP_EOL.$confphp.PHP_EOL. '?>';
			
			if(f::write(PATH_CONF, $confphp)) str::display('Mise à jour ' .$torev. ' effectuée', 'success');
			else str::display('Erreur lors de la mise-à-jour vers ' .$torev, 'error');
		}
	}

	// Remplace des parties de code
	static function codematch($search, $replace)
	{
		$searchLine = '#' .$search. '.+\n#';
		$search = '#' .$search. '#';
		$pages = glob('{index.php,pages/*,' .PATH_COMMON. 'php}', GLOB_BRACE);
		
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