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
	static function get($get = NULL, $default = NULL)
	{
		// Affichage du titre
		$pageVoulue = navigation::$page;
		$current = navigation::current();
		
		if(!$get) return self::$meta;
		if($get == 'titre')
		{
			$title = ($pageVoulue == 'admin' and get('admin'))
				? 'Gestion ' .ucfirst(get('admin'))
				: l::get('menu-' .$current, l::get('menu-' .$pageVoulue, ucfirst($pageVoulue)));
			$current_title = a::get(a::get(self::$meta, $current), 'titre');
			
			if($title and $current_title) $title = $title. ' - ' .$current_title;
			elseif(!$title and $current_title) $title = $current_title;
			
			self::$meta[$current]['titre'] = $title;
		}
		
		return (isset(self::$meta[$current][$get]) and !empty(self::$meta[$current][$get]))
			? ucfirst(str::accents(a::get(self::$meta[$current], $get, $default)))
			: $default;
	}
	
	// Renvoit les données meta d'une page
	static function page($page)
	{		
		if(isset(self::$meta[$page]) and !empty(self::$meta[$page])) return self::$meta[$page];
		else return array();
	}
}
?>