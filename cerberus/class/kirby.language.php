<?php
class l
{
	public static $lang = array();

	// Chargement du fichier langue
	function __construct($database = 'cerberus_langue')
	{
		if(SQL)
			if(!db::is_table('cerberus_langue'))
				update::table('cerberus_langue');		
		
		// Langue du site
		if(!s::get('langueSite')) s::set('langueSite', config::get('langue_default', 'fr'));
		if(get('langue')) self::change(get('langue'));

		// Langue de l'administration
		if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = config::get('langue_default', 'fr');
		if(isset($_GET['adminLangue']) && in_array($_GET['adminLangue'], config::get('langues'))) $_SESSION['admin']['langue'] = $_GET['adminLangue'];
		
		self::locale();
	
		// Chargement du fichier de langue et mise en cache
		$current = self::current();
		$filename = 'cerberus/cache/lang-{langue}.json';
		self::load('cerberus/include/cerberus.{langue}.json');

		// Chargement et création dynamique du fichier langue
		$tables = SQL ? db::field('cerberus_langue', 'tag') : FALSE;
		if(!empty($tables))
		{
			$index = self::load($filename);
			if(!$index and SQL)
			{
				// Récupération de la base de langues
				$index = db::select($database, 'tag,'.self::current(), NULL, 'tag ASC');
				$index = a::simple(a::rearrange($index, 'tag', true), false);
				
				if(isset($index) and !empty($index))
				{
					self::$lang = $index;
					if(CACHE) f::write($filename, json_encode($index));
				}
				else errorHandle('Fatal Error', 'Impossible de localiser le fichier langue', __FILE__, __LINE__);
			}
		}
	}

	// Charger un fichier langue
	static function load($fileraw)
	{
		$file = str_replace('{langue}', l::current(), $fileraw);
		$index = f::read($file, 'json');
		if(!empty($index))
		{
			self::$lang = array_merge(self::$lang, $index);
			return l::get();
		}
		else
		{
			return false;
			errorHandle('Fatal Error', 'Impossible de localiser le fichier langue', __FILE__, __LINE__);
		}
	}

	/*
	########################################
	########## LANGUE EN COURS #############
	########################################
	*/
	
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
	
	// Langue en cours dans l'administration
	static function admin_current()
	{
		if(isset($_SESSION['admin']['langue'])) return $_SESSION['admin']['langue'];
		else return NULL;
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

	/*
	########################################
	###### CONTENU ET TRADUCTIONS ##########
	########################################
	*/
	
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
			if($default === NULL) $default = ucfirst($key);
			$translate = a::get(self::$lang, $key, $default);
			return (empty($translate)) ? $default : stripslashes($translate);
		}
	}
	
	// Récupérer une traduction dans une langue en particulier
	static function getalt($key, $language = NULL, $default = NULL, $fallback = false)
	{
		$translate = db::field('cerberus_langue', $language, array('tag' => $key));
		if(!$translate)
			$translate = $fallback ? l::get($key, $default) : $default;
		
		return stripslashes($translate);
	}
	
	// Traduction d'un jour
	static function day($day = NULL)
	{
		if(!$day) $day = date('Y-m-d');
		$day = strtotime($day);
		return strtolower(strftime('%A', $day));
	}
	
	// Traduction d'un mois
	static function month($month = NULL)
	{
		if(!$month) $month = date('Y-m-d');
		$month = strtotime($month);
		return strftime('%B', $month);
	}
	
	// Charger du contenu traduit
	static function content($file)
	{
		$file = 'pages/text/' .self::current(). '-' .$file;
		
		if(f::inclure($file.'.html')) true;
		elseif(f::inclure($file.'.php')) true;
		else echo '<span style="color:red">[' .$file. '(' .self::current(). ')]</span>';
	}
}
?>