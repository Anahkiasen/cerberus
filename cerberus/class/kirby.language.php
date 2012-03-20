<?php
/**
 * 
 * Language
 * 
 * Some handy methods to handle multi-language support
 * 
 * @todo rework all but set() and get()
 * @package Kirby
 */
class l
{
	/**
	 * The global language array
	 * 
	 * @var array
	 */
	public static $lang = array();

	//// Initialise le fonctionnement des langues du site
	function __construct($database = 'cerberus_langue')
	{
		if(SQL)
			if(!db::is_table('cerberus_langue'))
				update::table('cerberus_langue');		
		
		// Langue du site
		if(!session::get('langue_site')) session::set('langue_site', config::get('langue_default', 'fr'));
		if(get('langue')) self::change(get('langue'));

		// Langue de l'administration
		if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = config::get('langue_default', 'fr');
		if(isset($_GET['get_admin_langue']) && in_array($_GET['get_admin_langue'], config::get('langues'))) $_SESSION['admin']['langue'] = $_GET['get_admin_langue'];
		
		self::locale();
	
		// Chargement du fichier de langue et mise en cache
		self::change(self::sanitize(self::current()));
		$index = cache::fetch('lang');
		
		if(!$index)
		{
			// Chargement et création dynamique du fichier langue
			$tables = SQL ? db::field('cerberus_langue', 'tag') : FALSE;
			if(!empty($tables) and SQL)
			{
				// Récupération de la base de langues
				$index = db::select($database, 'tag,'.self::current(), NULL, 'tag ASC');
				$index = a::simplify(a::rearrange($index, 'tag', true), false);
				
				if(isset($index) and !empty($index))
					self::$lang = $index;
				else errorHandle('Fatal Error', 'Impossible de localiser le fichier langue', __FILE__, __LINE__);
			}
			
			// Clés de langue par défaut
			self::load('cerberus/include/cerberus.{langue}.json');
			cache::fetch('lang', self::$lang);
		}			
	}

	/**
		* Loads a language file
		*/	
	static function load($fileraw)
	{
		$file = str_replace('{langue}', l::current(), $fileraw);
		$index = f::read($file, 'json');
		if(!empty($index))
		{
			self::$lang = array_merge(self::$lang, $index);
			return self::$lang;
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
	
	/* Affiche un lien vers une ou la totalité des langues */
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
	
	/**
		* Change the current website language
		*/	
	static function change($langue = 'fr')
	{
		session::set('langue_site', l::sanitize($langue));
		return session::get('langue_site');
	}
	
	/**
		* Returns the current website language
		*/	
	static function current()
	{
		if(session::get('langue_site')) return session::get('langue_site');
		else
		{
			$langue = str::split(server::get('http_accept_language'), '-');
			$langue = str::trim(a::get($langue, 0));			
			$langue = l::sanitize($langue);
			
			session::set('langue_site', $langue);
			return $langue;
		}
	}
	
	/**
	 * Returns the current language in the website administration
	 * 
	 * @return string 	The current language ID
	 */
	static function admin_current()
	{
		return session::get('admin,langue');
	}
	
	/**
		* Sets the language according to the user locale environnement
		*/	
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

	/**
		* Checks if a given language can be used in the current website
		*/	
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
	
	/** 
		* Sets a language value by key
		*
		* @param	mixed	 $key The key to define
		* @param	mixed	 $value The value for the passed key
		*/	
	static function set($key, $value = NULL)
	{
		if(is_array($key)) self::$lang = array_merge(self::$lang, $key);
		else self::$lang[$key] = $value;
	}
	
	/**
		* Gets a language value by key
		* [CERBERUS-EDIT]
		*
		* @param	mixed		$key The key to look for. Pass false or null to return the entire language array. 
		* @param	mixed		$default Optional default value, which should be returned if no element has been found
		* @return mixed
		*/
	static function get($key = NULL, $default = NULL)
	{
		if(empty($key)) return self::$lang;
		else
		{
			if($default === NULL) $default = ucfirst($key);
			$translate = a::get(self::$lang, $key, $default);
			return (empty($translate) or is_array($translate)) ? $default : stripslashes($translate);
		}
	}
	
	/**** Récupérer une traduction dans une langue en particulier */
	static function getalt($key, $language = NULL, $default = NULL, $fallback = false)
	{
		$translate = $language ? db::field('cerberus_langue', $language, array('tag' => $key)) : NULL;
		if(!$translate)
			$translate = $fallback ? l::get($key, $default) : $default;		
		return stripslashes($translate);
	}
	
	/**** Traduction d'un jour */
	static function day($day = NULL)
	{
		if(!$day) $day = date('Y-m-d');
		$day = strtotime($day);
		return strtolower(strftime('%A', $day));
	}
	
	/**** Traduction d'un mois */
	static function month($month = NULL)
	{
		if(!$month) $month = date('Y-m-d');
		$month = strtotime($month);
		return strftime('%B', $month);
	}
	
	/**** Charger du contenu traduit */
	static function content($file)
	{
		$file = 'pages/text/' .self::current(). '-' .$file;
		
		if(f::inclure($file.'.html')) true;
		elseif(f::inclure($file.'.php')) true;
		else echo '<span style="color:red">[' .$file. '(' .self::current(). ')]</span>';
	}
}
?>