<?php
class meta
{
	public static $meta = array();
	
	// Fonction META 
	static function build()
	{
		// Tableau des informations META
		$metafile = 'cerberus/cache/meta-' .l::current(). '.php';
		$db_exist = SQL ? db::is_table('cerberus_meta', 'cerberus_structure') : FALSE;
		$meta = f::read($metafile, 'json');
		
		if(!self::$meta and SQL and (config::get('meta', FALSE) or $db_exist))
		{
			// Création des tables
			if(!db::is_table('cerberus_structure', 'cerberus_meta'))
			{
				update::table('cerberus_meta');
				update::table('cerberus_structure');
			}
			
			// Récupération des Metadata
			$metadata = db::left_join(
				'cerberus_meta M',
				'cerberus_structure S',
				'M.page = S.id',
				'S.page, S.parent, M.titre, M.description, M.url',
				array('langue' => l::current()));
				
			// Analyse et tri
			foreach($metadata as $values)
			{
				if(empty($values['description'])) $values['description'] = $values['titre'];
				if(empty($values['url'])) $values['url'] = str::slugify($values['titre']);
				
				self::$meta[$values['parent'].'-'.$values['page']] =
					array(	'titre' => $values['titre'],
							'description' => $values['description'],
							'url' => $values['url']);
			}

			// Mise en cache
			if(CACHE) f::write($metafile, json_encode($meta));
		}
	}

	// Renvoit un type de données précis
	static function get($get, $default = NULL)
	{
		// Affichage du titre
		global $desired;
		$pageVoulue = $desired->current(false);
		$current = $desired->current();
		
		if(isset(self::$meta[$current][$get]) and !empty(self::$meta[$current][$get]))
		{
			if($get == 'titre')
			{
				$title_prefix = ($pageVoulue == 'admin' and get('admin'))
					? 'Gestion ' .ucfirst(get('admin'))
					: l::get('menu-' .$current, l::get('menu-' .$pageVoulue, ucfirst($pageVoulue)));
				if(!empty($title_prefix) and $title_prefix != self::$meta[$current]['titre'])
					self::$meta[$current]['titre'] = $title_prefix. ' - ' .self::$meta[$current]['titre'];
			}
			return ucfirst(str::accents(a::get(self::$meta[$current], $get, $default)));
		}
		else return $default;
	}
	
	// Renvoit les données meta d'une page
	static function page($page)
	{
		global $meta;
		
		if(isset($meta[$page]) and !empty($meta[$page])) return $meta[$page];
		else return array();
	}
}
?>