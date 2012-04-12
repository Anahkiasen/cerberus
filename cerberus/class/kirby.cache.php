<?php
class cache
{
	// Cache parameters
	static private $cached_file = NULL;
	static private $folder = NULL;
	static private $time = NULL;
	
	static private $cache_get_variables = NULL;
	static private $get_remove = array('PHPSESSID', 'gclid');

	// Initialization
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
	 * 	cache::fetch('gallery');
	 * 		[your page]
	 * 	cache::save();
	 * 
	 * @param string 		$name The name of the cached file
	 * @param mixed 		$content Facultative; a piece of data to cache, can be a variable, an array etc.
	 * 						If left NULL, the cache function will start caching everything that is outputed after its call
	 * 						To save this output, call cache_save
	 * @param array 		$params Additional parameters to pass the function
	 *                      -- cache_type: if set to 'output', cache::fetch will initialize a content::start,
	 *                         and will save everything that comes after cache::fetch, until you do a cache::save
	 *                         If set to anything else (or not set), cache::fetch will
	 *                         save the given data on the spot without using an output buffer
	 * 						-- cache_folder: The folder where the cached file will be
	 * 						-- cache.time: How long you want to keep the cached version
	 * 						-- cache_variables: Appends the current $_GET variables to the name of the file, allowing caching of dynamic pages
	 * @return mixed 		If you're caching a piece of data, it will return the said piece of data.
	 * 						If you're caching the page, it will return a boolean stating if the file was cached or not
	 */
	static function fetch($name, $content = NULL, $params = array())
	{
		if(!CACHE) return false;
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
	 * @return mixed   The result of the cache
	 */
	static function page($page, $params = array())
	{
		$params = array_merge($params, array('type' => 'output'));
		return self::fetch($page, NULL, $params);
	}

	/**
	 * Saves an output buffer initiated with cache::fetch
	 * 
	 * @param boolean  $return Return the saved data or echoes it
	 * @return mixed   Echoes or return all the data that was just cached
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
	 * @param string    $search The key to look for
	 * @param boolean   If false returns the first file found, if true returns all files found
	 * @return mixed    FALSE if the file hasn't been found, the path if it has
	 */
	static function search($search, $all_files = false)
	{
		self::init();
		
		$file = glob(self::$folder.$search.'.{json,html}', GLOB_BRACE);
		if($all_files) return $file;
		return $file ? a::get($file, 0) : FALSE;
	}

	/**
	 * Deletes file(s) from the cache. The key passed can contain * and braces as it's parsed by glob()
	 * 
	 * @param string    $delete The keys to look for. If NULL, the function empties the cache folder
	 * @param boolean   $sloppy If true if will look for all files containing the key, if not it will search an exact match
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
}
?>