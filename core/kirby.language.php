<?php
class l
{
	public static $lang = array();

	// Chargement du fichier langue
	function __construct($database = 'langue')
	{
		$current = self::current();
		$filename = 'cerberus/cache/lang-' .$current. '.php';
	
		$tables = db::row('langue', 'tag');
		if(!empty($tables))
		{
			if(!PRODUCTION) sunlink($filename); // Suppression de la version existante
			if(file_exists($filename) and PRODUCTION) self::load($filename);
			else
			{
				// Récupération de la base de langues
				$index = db::select($database, '*', NULL, 'tag ASC');
				$index = a::rearrange($index, 'tag');
				
				if($index)
				{
					// Ecriture du fichier PHP
					$renderPHP = "<?php \n";
					foreach($index as $tag => $langues)
						$renderPHP .= '$lang[\'' .$tag. '\'] = \'' .addslashes($langues[$current]). "';\n";

					f::write($filename, $renderPHP. '?>');
				}
			}
			
			// Langue du site
			if(!s::get('langueSite')) s::set('langueSite', config::get('langue_default'));
			if(get('langue')) self::change(get('langue'));

			// Langue de l'administration
			if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = config::get('langue_default', 'fr');
			if(isset($_GET['getLangueAdmin']) && in_array($_GET['getLangueAdmin'], config::get('langues'))) $_SESSION['admin']['langue'] = $_GET['getLangueAdmin'];
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
		s::set('langueSite', l::sanitize($langue));
		return s::get('langueSite');
	}
	
	// Langue en cours
	static function current()
	{
		if(s::get('langueSite')) return s::get('langueSite');
		else
		{
			$langue = str::split(server::get('http_accept_language'), '-');
			$langue = str::trim(a::get($langue, 0));			
			$langue = l::sanitize($langue);
			
			s::set('langueSite', $langue);
			return $langue;
		}
	}
	
	// Langue autorisée ou non
	static function sanitize($langue)
	{
		$default = config::get('langue_default', 'fr');
		$array_langues = config::get('langues', array($default));
		
		if(!in_array($langue, $array_langues)) $langue = $default;
		return $langue;
	}

	// Charger un fichier langue
	static function load($fileraw)
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