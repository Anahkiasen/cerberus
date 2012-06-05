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
	 * @var array
	 */
	public static $lang = array();

	/**
	 * Loads the Cerberus language database
	 *
	 * @param  string $database The name of the cerberus database
	 */
	static function cerberus($database = 'cerberus_langue')
	{
		// If no active SQL connection, don't even bother
		if(!SQL) return false;

		// If no Cerberus database is found, create it
		if(!db::is_table('cerberus_langue')) update::table('cerberus_langue');

		// If we have no session, set default
		if(!session::get('langue_site')) session::set('langue_site', config::get('langue_default', 'fr'));

		// If we asked for a language, set it
		if(get('langue')) self::change(get('langue'));

		// Admin language
		if(!isset($_SESSION['admin']['langue'])) $_SESSION['admin']['langue'] = config::get('langue_default', 'fr');
		if(isset($_GET['get_admin_langue']) && in_array($_GET['get_admin_langue'], config::get('langues'))) $_SESSION['admin']['langue'] = $_GET['get_admin_langue'];

		// Attempt at loading a cached language file
		$index = cache::fetch('lang');

		// If a cached file was found
		if($index)
		{
			self::$lang = $index;
			return true;
		}

		// Gathering the different languages
		$tables = db::field('cerberus_langue', 'tag');
		if(!empty($tables))
		{
			// Getting the language array
			$index = db::select($database, 'tag,'.self::current(), NULL, 'tag ASC');
			$index = a::simplify(a::rearrange($index, 'tag', true), false);

			// If the array we got is allright, save it
			if(isset($index) and !empty($index)) self::$lang = array_merge(self::$lang, $index);

			// Otherwise something obviously went wrong
			else throw new Debug(l::get('language.missing'));
		}

		// Caching the final index
		cache::fetch('lang', self::$lang);

		self::locale();
	}

	/**
	 * Loads a language file
	 */
	static function load($fileraw)
	{
		$file = str_replace('{langue}', self::current(), $fileraw);
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

	//////////////////////////////////////////////////////////////////
	/////////////////////// CURRENT LANGUAGE /////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Displays a flag-based language selection menu
	 *
	 * @param  [type] $path   [description]
	 * @param  [type] $langue [description]
	 *
	 * @return [type]         [description]
	 */
	static function flags($path, $langue = NULL)
	{
		$array = ($langue) ? array(self::sanitize($langue)) : config::get('langues');
		if($langue != NULL or count($array) > 1) foreach($array as $langue)
		{
			$currentpath = $path;
			$hover = (self::current() == $langue) ? NULL : '-off';
			$currentpath = str_replace('{langue}', $langue, $path);
			$currentpath = str_replace('{hover}', $hover, $currentpath);

			return str::slink(
				NULL,
				str::img($currentpath, strtoupper($langue). ' VERSION'),
				array('langue' => $langue));
		}
	}

	/**
	 * Change the current website language
	 *
	 * @param  string   $langue  The desired language
	 * @return boolean           If the website successfully changed language
	 */
	static function change($langue = 'fr')
	{
		session::set('langue_site', self::sanitize($langue));
		return session::get('langue_site') == $langue;
	}

	/**
	 * Returns the current website language
	 *
	 * @return string Current website's language
	 */
	static function current()
	{
		// If we have a language in session
		if(session::get('langue_site')) return session::get('langue_site');

		// Else, attempt at guessing it
		$langue =  str::split(server::get('http_accept_language'), '-');
		$langue =  str::trim(a::get($langue, 0));
		$langue = self::sanitize($langue);

		// Set new session
		session::set('langue_site', $langue);
		return $langue;
	}

	/**
	 * Returns the current language in the website administration
	 *
	 * @return string  The current language ID
	 */
	static function admin_current()
	{
		return session::get('admin,langue');
	}

	/**
	 * Sets the locale according to the current language
	 *
	 * @param  string  $language  A language string to use
	 * @return
	 */
	static function locale($language = FALSE)
	{
		// If nothing was given, just stay where we are
		if(!$language) $language = self::current();

		// Base table of languages
		$default_locales = array(
			'de' => array('de_DE.UTF8','de_DE@euro','de_DE','de','ge'),
			'fr' => array('fr_FR.UTF8','fr_FR','fr'),
			'es' => array('es_ES.UTF8','es_ES','es'),
			'it' => array('it_IT.UTF8','it_IT','it'),
			'pt' => array('pt_PT.UTF8','pt_PT','pt'),
			'zh' => array('zh_CN.UTF8','zh_CN','zh'),
			'en' => array('en_US.UTF8','en_US','en'),
		);

		// Additional languages from the config file
		$locales = config::get('locales', array());
		$locales = array_merge($default_locales, $locales);

		// Set new locale
		setlocale(LC_ALL, a::get($locales, $language, array('en_US.UTF8','en_US','en')));
		return setlocale(LC_ALL, 0);
	}

	/**
	 * Checks if a given language can be used in the current website
	 *
	 * @param  string   $language A language to check
	 * @return boolean  Language authorized or not
	 */
	static function sanitize($language)
	{
		$default = config::get('langue_default', 'fr');
		$array_langues = config::get('langues', array($default));

		if(!in_array($language, $array_langues)) $language = $default;
		return $language;
	}

	//////////////////////////////////////////////////////////////////
	/////////////////////////// CONTENT //////////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Sets a language value by key
	 *
	 * @param mixed  $key    The key to define
	 * @param mixed  $value  The value for the passed key
	 */
	static function set($key, $value = NULL)
	{
		if(is_array($key)) self::$lang = array_merge(self::$lang, $key);
		else self::$lang[$key] = $value;
	}

	/**
	 * Gets a language value by key
	 *
	 * @param  mixed  $key      The key to look for. Pass false or null to return the entire language array.
	 * @param  mixed  $default  Optional default value, which should be returned if no element has been found
	 * @return mixed
	 * @package       Cerberus,Kirby
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

	/**
	 * Gets a translation in a particular language
	 *
	 * @param  string  $key       The key to look for
	 * @param  string  $language  The language you want it in
	 * @param  string  $default   A default fallback string if translation not found
	 * @param  boolean $fallback  If true and translation not found, will fall back to default language
	 *
	 * @return string            A translation
	 */
	static function getTranslation($key, $language = NULL, $default = NULL, $fallback = false)
	{
		$translate = $language ? db::field('cerberus_langue', $language, array('tag' => $key)) : NULL;
		if(!$translate)
			$translate = $fallback ? self::get($key, $default) : $default;
		return stripslashes($translate);
	}

	/**
	 * Translate the name of a day
	 *
	 * @param  int     $day  A given date
	 * @return string  The day's name if the current language
	 */
	static function day($day = NULL)
	{
		if(!$day) $day = date('Y-m-d');
		$day = strtotime($day);
		return strtolower(strftime('%A', $day));
	}

	/**
	 * Translate the name of a month
	 *
	 * @param  int     $month  A given date
	 * @return string  The month's name if the current language
	 */
	static function month($month = NULL)
	{
		if(!$month) $month = date('Y-m-d');
		$month = strtotime($month);
		return strftime('%B', $month);
	}

	/**
	 * Attempts to load editorial text for a given page
	 *
	 * @param  string  $file The name of the translated file
	 * @return mixed   The content of the file, or an error if not found
	 */
	static function content($content)
	{
		$file = 'pages/text/' .self::current(). '-' .$content;
		$file = f::exist($file.'.html', $file.'.php');

		if($file) include $file;
		else str::display('Translation for ' .$content. ' not found in (' .self::current(). ')', 'error');
	}
}
