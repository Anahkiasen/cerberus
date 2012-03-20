<?php
class meta
{
	public static $meta = NULL;
	
	private static $file;
	private static $overwrite = array();
	
	/* 
	########################################
	############ INITIALISATION ############
	########################################
	*/
	
	static function build()
	{
		// Tableau des informations META
		self::$file = PATH_CACHE. 'meta-' .l::current(). '.json';
		$db_exist = SQL ? db::is_table(array('cerberus_meta', 'cerberus_structure')) : FALSE;
		$meta = cache::fetch('meta');
		
		// Si aucune données META en cache, création du tableau
		if(!$meta and SQL and (config::get('meta', FALSE) or $db_exist))
		{
			// Création des tables
			if(!$db_exist)
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
				
				$variables = array('titre', 'description', 'url');
				foreach($variables as $v)
				{
					$page = $values['parent'].'-'.$values['page'];
					self::$meta[$page][$v] = a::get($values, $v);
				}
			}
		}
		else self::$meta = array();
	}

	/* 
	########################################
	######## MODIFIER LES DONNEES ##########
	########################################
	*/

	// Modifier les données META
	static function set($key, $value = NULL)
	{
		self::$overwrite[$key] = $value;
	}
	
	// Créer un nuage de mots-clés
	static function keywords($string)
	{
		$string = preg_replace('#([,\.\r\n\-])#', NULL, $string);
		$string = explode(' ', $string);
		shuffle($string);
		$string = array_filter(array_unique($string));
		$string = implode(', ', $string);
		return $string;
	}
	
	/* 
	########################################
	######## RENVOYER LES DONNEES ##########
	########################################
	*/

	// Renvoit un type de données précis
	static function get($get = NULL, $default = NULL)
	{
		// Affichage du titre
		$current = navigation::current();
		
		if(!$get) return self::$meta;
		if($get == 'titre')
		{
			$title = (navigation::$page == 'admin' and get('admin'))
				? 'Gestion ' .ucfirst(get('admin'))
				: l::get('menu-' .$current, l::get('menu-' .navigation::$page, ucfirst(navigation::$page)));
			$current_title = isset(self::$meta[$current]['titre']) ? self::$meta[$current]['titre'] : NULL;
			
			if($title and $current_title) $title = $title. ' - ' .$current_title;
			elseif(!$title and $current_title) $title = $current_title;
			
			self::$meta[$current]['titre'] = $title;
		}
		
		return (isset(self::$meta[$current][$get]) and !empty(self::$meta[$current][$get]))
			? ucfirst(str::accents(a::get(self::$meta[$current], $get, $default)))
			: $default;
	}
	
	// Renvoit les données meta d'une page
	static function page($page = NULL)
	{		
		if(isset(self::$meta[$page]) and !empty(self::$meta[$page])) return self::$meta[$page];
		else return array(navigation::current());
	}
	
	// Renvoit une ou la totalité des balises META
	static function head($key = NULL)
	{
		if(!is_array(self::$meta)) self::build();
		
		if($key)
		{
			// Récupération de la balise
			$return = NULL;
			$value = self::get($key);
			$value = str_replace('{meta}', $value, a::get(self::$overwrite, $key, '{meta}'));
			
			if($value)
			{
				$return .= "\t";
				
				if($key == 'titre' and $value) $return .= '<title>' .$value. '</title>';
				else $return .= '<meta name="' .$key. '" content="' .$value. '" />';
				
				$return .= PHP_EOL;
			}
			
			return $return;
		}
		else echo PHP_EOL.'<head>'.PHP_EOL.self::head('titre').self::head('description').self::head('keywords');
		
		// Mise en cache
		cache::fetch('meta', self::$meta);
	}
}
?>