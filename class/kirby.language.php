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
			if(!PRODUCTION or LOCAL) f::remove($filename); // Suppression de la version existante
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
			if(!s::get('langueSite')) s::set('langueSite', config::get('langue_default', 'fr'));
			if(get('langue')) self::change(get('langue'));

			// Langue de l'administration
			if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = config::get('langue_default', 'fr');
			if(isset($_GET['adminLangue']) && in_array($_GET['adminLangue'], config::get('langues'))) $_SESSION['admin']['langue'] = $_GET['adminLangue'];
			
			self::locale();
		}
	}

	// Changer une traduction
	static function set($key, $value = NULL)
	{
		if(is_array($key)) self::$lang = array_merge(self::$lang, $key);
		else self::$lang[$key] = $value;
	}
	
	// Récupérer une traduction
	static function get($key = NULL, $default = NULL)
	{
		if(empty($key)) return self::$lang;
		else
		{
			$translate = a::get(self::$lang, $key, $default);
			return (empty($translate)) ? $default : stripslashes($translate);
		}
	}
	
	// Affiche un lien vers une ou la totalité des langues
	static function flags($path, $langue = NULL)
	{
		$array = ($langue) ? array(self::sanitize($langue)) : config::get('langues');
		if($langue != NULL or count($array) > 1) foreach($array as $langue)
		{
			$currentpath = $path;
			$hover = (self::current() == $langue) ? NULL : '-off';
			$currentpath = str_replace('{langue}', $langue, $path);
			$currentpath = str_replace('{hover}', $hover, $currentpath);
			
			echo str::slink(
				NULL,
				str::img($currentpath, strtoupper($langue). ' VERSION'),
				array('langue' => $langue));
		}
	}
	
	
	// Changer de langue
	static function change($langue = 'fr')
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
	
	// Règle l'environnement dans la langue correcte
	static function locale($language = FALSE)
	{
		if(!$language) $language = l::current();
		$default_locales = array(
			'de' => array('de_DE.UTF8','de_DE@euro','de_DE','de','ge'),
			'fr' => array('fr_FR.UTF8','fr_FR','fr'),
			'es' => array('es_ES.UTF8','es_ES','es'),
			'it' => array('it_IT.UTF8','it_IT','it'),
			'pt' => array('pt_PT.UTF8','pt_PT','pt'),
			'zh' => array('zh_CN.UTF8','zh_CN','zh'),
			'en' => array('en_US.UTF8','en_US','en'),
		);
		$locales = config::get('locales', array());
		$locales = array_merge($default_locales, $locales);
		setlocale(LC_ALL, a::get($locales, $language, array('en_US.UTF8','en_US','en')));
		return setlocale(LC_ALL, 0);
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
		if(!file_exists($file))
			$file = str_replace('{langue}', config::get('langue_default', 'fr'), $fileraw);
		
		if(file_exists($file))
		{
			include($file);
			self::$lang = $lang;
			return l::get();
		}
	}
	
	// Traduction d'un jour
	static function day($day)
	{
		$day = strtotime($day);
		return strftime('%A', $day);
	}
	
	// Traduction d'un mois
	static function month($month)
	{
		$month = strtotime($month);
		return strftime('%B', $month);
	}
	
	// Charger du contenu traduit
	static function content($file)
	{
		$file = 'pages/text/' .self::current(). '-' .$file;
		
		$page = f::inclure($file.'.html');
		if(!$page) $page = f::inclure($file.'.php');
		if(!$page) echo '<span style="color:red">[' .$file. '(' .self::current(). ')]</span>';
	}
}
?>