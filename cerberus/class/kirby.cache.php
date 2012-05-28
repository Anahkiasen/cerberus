<?php
/**
 *
 * Cache
 *
 * Use for the caching of data, pieces of pages or complete pages
 * It can stock variables, arrays, and use the Content class to stock anything else
 *
 * @package Cerberus
 */
class cache
{
  /**
   * The name of the current output buffer being cache, initialized by fetch() and retrieved by save()
   * @var string
   */
  static private $cached_file = NULL;

  /**
   * The folder where cached files go
   * @var string
   */
  static private $folder = NULL;

  /**
   * The amount of time in seconds to cache files
   * @var int
   */
  static private $time = NULL;

  /**
   * Cache current GET variables or not (useful to cache dynamic pages)
   * @var boolean
   */
  static private $cache_get_variables = FALSE;

  /**
   * The GET variables to avoid caching
   * @var array
   */
  static private $get_remove = array('PHPSESSID', 'gclid');

  /**
   * Initialize the cache class, will go fetch the cache parameters in the config once
   */
	static function init()
	{
		if(!self::$folder)
		{
			self::$folder = config::get('cache_folder', PATH_CACHE);
			self::$time = config::get('cache.time', 60 * 60 * 24 * 365);
			self::$cache_get_variables = config::get('cache.get_variables');
		}
	}

	/**
	 * Puts data into a cache
	 *
	 * Can cache data
	 * 	$array = cache::fetch('data');
	 * 	if(!$array) $array = cache::fetch('data', $data)
	 *
	 * Or pages
	 * 	cache::page('gallery');
	 * 		[your page]
	 * 	cache::save();
	 *
	 * @param string  $name     The name of the cached file
	 * @param mixed   $content  Facultative; a piece of data to cache, can be a variable, an array etc.
	 *                            If left NULL, the cache function will start caching everything that is outputed after its call
	 *                            To save this output, call cache_save
	 * @param array  $params    Additional parameters to pass the function
	 *                            -- cache_type: if set to 'output', cache::fetch will initialize a content::start,
	 *                              and will save everything that comes after cache::fetch, until you do a cache::save
	 *                              If set to anything else (or not set), cache::fetch will
	 *                              save the given data on the spot without using an output buffer
	 *                            -- cache_folder: The folder where the cached file will be
	 *                            -- cache.time: How long you want to keep the cached version
	 *                            -- cache_variables: Appends the current $_GET variables to the name of the file, allowing caching of dynamic pages
	 * @return mixed  If you're caching a piece of data, it will return the said piece of data.
	 *                If you're caching the page, it will return a boolean stating if the file was cached or not
	 */
	static function fetch($name, $content = NULL, $params = array())
	{
		if(!CACHE and !a::get($params, 'cache.force')) return false;
		self::init();

		$time = a::get($params, 'cache.time', self::$time);
		$cache_get_variables = a::get($params, 'cache.get_variables', self::$cache_get_variables);
		$name = l::current(). '-' .str::slugify($name);
		$get_remove = a::get($params, 'get_remove', self::$get_remove);
		$cache_output = (a::get($params, 'type') == 'output');

		// Cache GET variables to allow for caching of dynamic pages
		if($cache_get_variables and $cache_output)
		{
			$array_var = is_array($cache_get_variables) ? $cache_get_variables : $_GET;
			$array_var = a::remove($array_var, $get_remove);

			$forbidden_var = array('http', '/', '\\');
			if($array_var)
				foreach($array_var as $var_key => $var_val)
					if(!str::find($forbidden_var, $var_val) and !empty($var_val))
						$name .= '-'.$var_key .'-' .$var_val;
		}

		// Looking for a cached file
		$modified_source = time();
		$extension = ($content and !$cache_output) ? 'json' : 'html';

		$file = self::search($name. '-[0-9]*');
		if($file)
		{
			$modified = explode('-', $file);
			$modified = a::last($modified);

			// If source file has been updated
			$modified_source = isset($params['source'])
				? filemtime(a::get($params, 'source'))
				: $modified;

			if($modified == $modified_source and (time() - filemtime($file)) <= self::$time) $cached = $file;
			else f::remove($file);
		}

		// If no cached file found, we create one
		if(!isset($cached))
			$cached = self::$folder.$name.'-'.$modified_source.'.'.$extension;

		// Caching of a page or data
		if($cache_output and !$content)
		{
			self::$cached_file = $cached;
			if(file_exists(self::$cached_file))
			{
				content::load(self::$cached_file, false);
				exit();
			}
			else content::start();
			return file_exists($cached);
		}
		elseif($content or file_exists($cached))
		{
			if(file_exists($cached)) $content = f::read($cached, 'json');
			else f::write($cached, json_encode($content));
			return $content;
		}
		else return false;
	}

	/**
	 * Shortcut to cache a page
	 *
	 * @param  string  $page    The page name
	 * @param  array   $params  Additional parameters to pass the cache function
	 * @return string  The content of the page
	 */
	static function page($page, $params = array())
	{
		$params = array_merge($params, array('type' => 'output'));
		return self::fetch($page, NULL, $params);
	}

	/**
	 * Saves an output buffer initiated with cache::fetch
	 *
	 * @param  boolean  $return Return the saved data or echoes it
	 * @return mixed    Echoes or return all the data that was just cached
	 */
	static function save($return = false)
	{
		if(self::$cached_file)
		{
			$content = content::end(TRUE);
			f::write(self::$cached_file, $content);
			self::$cached_file = NULL;

			if($return) return $content;
			else echo $content;
		}
	}

	/**
	 * Search for files inside the cache
	 *
	 * @param  string   $search     The key to look for
	 * @param  boolean  $all_files  If false returns the first file found, if true returns all files found
	 * @param  boolean  $sloppy     Allows for sloppy search (places the query in wildcards)
	 * @return mixed    FALSE if the file hasn't been found, the path if it has
	 */
	static function search($search, $all_files = false, $sloppy = false)
	{
		self::init();
		if($sloppy) $search = '*-' .$search. '-*';

		$file = glob(self::$folder.$search.'.{json,html}', GLOB_BRACE);
		if($all_files) return $file;
		return $file ? a::get($file, 0) : FALSE;
	}

	/**
	 * Deletes file(s) from the cache. The key passed can contain * and braces as it's parsed by glob()
	 *
	 * @param  string   $delete  The keys to look for. If NULL, the function empties the cache folder
	 * @param  boolean  $sloppy  If true if will look for all files containing the key, if not it will search an exact match
	 * @return boolean  True if the file(s) have been correctly removed, false if not found
	 */
	static function delete($delete = NULL, $sloppy = FALSE)
	{
		if(!$delete) $delete = '*';
		if($sloppy) $delete = '*-'.$delete.'-*';

		$files = self::search($delete, true);
		if($files) foreach($files as $file) f::remove($file);
		else return FALSE;
	}

	/**
	 * Purges the project of all files cache-related
	 */
	static function purge()
	{
		// List the files to purge
		$purge_files = array(
			PATH_CERBERUS.'config.rb',
			'config.rb',
			PATH_CACHE,
			PATH_CERBERUS.'{js}/{plugins}/',
			PATH_CERBERUS.'{css}/{plugins}/',
			PATH_CERBERUS.'.sass-cache/',
			PATH_COMMON  .'.sass-cache/');

		foreach($purge_files as $f)
		{
			// Replace aliases with real folder names
			$f = dispatch::path($f);
			if(!file_exists($f)) continue;

			// Remove files or folders
			if(is_dir($f)) dir::remove($f);
			else f::remove($f);
		}
	}

	/**
	 * Creates an Application Cache manifest according to a list of given ressources and fallbacks
	 *
	 * @param  array    $cache     An array containing the assets to cache
	 * @param  mixed    $network   An array listing online assets
	 * @param  array    $fallback  An array containing fallback assets if offline
	 * @return boolean  Success of the cache.manifest writing
	 */
	static function manifest($cache = array('img', 'fonts', 'video'), $network = '*', $fallback = NULL)
	{
		if(!file_exists('cache.manifest'))
		{
			$manifest = 'CACHE MANIFEST'.PHP_EOL.PHP_EOL;

			// CSS/JS
			$manifest .= 'CACHE:'.PHP_EOL;
			$manifest .= PHP_EOL.'# JS'.PHP_EOL;
				foreach(dispatch::currentJS() as $js)
				{
					if(str::find('http', $js)) $network[] = $js;
					else $manifest .= $js.PHP_EOL;
				}
			$manifest .= PHP_EOL.'# CSS'.PHP_EOL;
				foreach(dispatch::currentCSS() as $css)
				{
					if(str::find('http', $css)) $network[] = $css;
					else $manifest .= $css.PHP_EOL;
				}

			// Détermination des ressources
			$glob = glob('{assets/{common}/{' .implode(',', $cache). '}/{*,*/*},pages/*.html}', GLOB_BRACE);
			$glob = array_merge($glob, glob('assets/cerberus/img/rgbapng/*'));
			if(dispatch::isScript('iconic'))
				$glob = array_merge($glob, glob('assets/cerberus/fonts/*'));

			// Listing des ressources
			foreach($glob as $g)
				if(!is_dir($g)) $files_sorted[dirname($g)][] = $g;
			foreach($files_sorted as $t => $files)
			{
				$manifest .= PHP_EOL.'# '.strtoupper($t).PHP_EOL;
				foreach($files as $f) $manifest .= $f.PHP_EOL;
			}

			// Network
			$manifest .= PHP_EOL.'NETWORK:'.PHP_EOL.PHP_EOL;
			if(!is_array($network)) $manifest .= $network;
			else foreach($network as $n) $manifest .= $n.PHP_EOL;

			// Network
			if($fallback)
			{
				$manifest .= PHP_EOL.'FALLBACK:'.PHP_EOL.PHP_EOL;
				if(!is_array($fallback)) $manifest .= $fallback;
				else foreach($fallback as $from => $to) $manifest .= $from.' '.$to.PHP_EOL;
			}

			return f::write('cache.manifest', $manifest);
		}
	}
}
?>
