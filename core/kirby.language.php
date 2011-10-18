<?php
class l
{
	public static $lang = array();

	// Chargement du fichier langue
	function __construct($database = 'langue')
	{
		$current = self::current();
		$filename = 'cerberus/cache/lang-' .$current. '.php';
	
		$tables = mysqlQuery('SELECT tag FROM langue LIMIT 1');
		if(!empty($tables))
		{
			if(!PRODUCTION) sunlink($filename); // Suppression de la version existante
			if(file_exists($filename) and PRODUCTION) self::load($filename);
			else
			{
				// Récupération de la base de langues
				$thisIndex = mysqlQuery('SELECT * FROM ' .$database. ' ORDER BY tag ASC', true, 'tag');
				if($thisIndex)
				{
					// Ecriture du fichier PHP
					$renderPHP = "<?php \n";
					foreach($thisIndex as $tag => $langues)
					{
						$renderPHP .= '$lang[\'' .$tag. '\'] = \'' .addslashes($langues[$current]). "';\n";
					}	
					f::write($filename, $renderPHP. '?>');
				}
			}
			
			// Langue du site
			if(!s::get('langue')) s::set('langue', config::get('langue_default'));
			if(isset($_GET['getlangue']) && in_array($_GET['langue'], $config::get('langues'))) s::set('langue', $_GET['getlangue']);
			
			// Langue de l'administration
			if(!s::get('langueAdmin')) s::set('langueAdmin', config::get('langue_default'));
			if(isset($_GET['getLangueAdmin']) && in_array($_GET['getLangueAdmin'], $config::get('langues'))) s::set('langueAdmin', $_GET['getLangueAdmin']);
		}
	}

	// Changer une traduction
	function set($key, $value = NULL)
	{
		if(is_array($key)) self::$lang = array_merge(self::$lang, $key);
		else self::$lang[$key] = $value;
	}
	
	// Récupérer une traduction
	static function get($key = NULL, $default = NULL)
	{
		if(empty($key)) return self::$lang;
		else return a::get(self::$lang, $key, $default);
	}
	
	// Changer de langue
	function change($langue = 'fr')
	{
		s::set('langue', l::strip($langue));
		return s::get('langue');
	}
	
	// Langue en cours
	static function current()
	{
		if(s::get('langue')) return s::get('langue');
		else
		{
			$langue = str::split($_SERVER['HTTP_ACCEPT_LANGUAGE'], '-');
			$langue = str::trim(a::get($langue, 0));
			$langue = l::sanitize($langue);
			
			s::set('langue', $langue);
			return	$langue;
		}
	}
	
	// Langue autorisée ou non
	function sanitize($langue)
	{
		$default = config::get('langue_default');
		$array_langues = config::get('langues', array($default));
		
		if(!in_array($langue, $array_langues)) $langue = config::get('langues', $default);
		return $langue;
	}

	// Charger un fichier langue
	function load($fileraw)
	{
		$file = str_replace('{langue}', l::current(), $fileraw);
		if(!file_exists($file)) $file = str_replace('{langue}', config::get('langue_default', 'fr'), $fileraw);
		
		if(file_exists($file))
		{
			require($file);
			self::$lang = $lang;
			return l::get();
		}
	}
	
	// Charger du contenu traduit
	static function content($file)
	{
		$file = 'pages/text/' .self::current(). '-' .$file. '.html';
		if(file_exists($file)) include $file;
		else echo '<span style="color:red">[' .$file. '(' .self::current(). ')]</span>';
	}
}
?>