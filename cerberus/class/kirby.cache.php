<?php
class cache
{
	// Cache parameters
	static private $cached_file = NULL;
	static private $cache_folder = NULL;
	static private $cache_time = NULL;
	static private $cache_variables = NULL;

	/**
	 * Puts data into a cache
	 * 
	 * Can cache data
	 * 	$array = cache_fetch('data');
	 * 	if(!$array) $array = ...
	 * 
	 * Or pages
	 * 	cache_fetch('gallery');
	 * 		[your page]
	 * 	cache_save();
	 * 
	 * @param string 		$name The name of the cached file
	 * @param mixed 		$content Facultative; a piece of data to cache, can be a variable, an array etc.
	 * 						If left NULL, the cache function will start caching everything that is outputed after its call
	 * 						To save this output, call cache_save
	 * @param array 		$params Additional parameters to pass the function
	 * 						cache_folder: The folder where the cached file will be
	 * 						cache_time: How long you want to keep the cached version
	 * 						cache_variables: Appends the current $_GET variables to the name of the file, allowing caching of dynamic pages
	 * @return mixed 		If you're caching a piece of data, it will return the said piece of data.
	 * 						If you're caching the page, it will return a boolean stating if the file was cached or not
	 */
	static function fetch($name, $content = NULL, $params = array())
	{
		// Initialization
		if(!self::$cache_folder)
		{
			self::$cache_folder = config::get('cache_folder', 'cerberus/cache/');
			self::$cache_time = config::get('cache_time', 60 * 60 * 24 * 365);
			self::$cache_variables = config::get('cache_variables', TRUE);			
		}
				
		$cache_time = a::get($params, 'cache_time', self::$cache_time);
		$cache_variables = a::get($params, 'cache_variables', self::$cache_variables);
		$name = l::current(). '-' .str::slugify($name);
		
		// Cache GET variables to allow for caching of dynamic pages
		if($cache_variables)
		{
			$array_var = is_array($cache_variables) ? $cache_variables : $_GET;
			$array_var = a::remove($array_var, array('PHPSESSID', 'gclid'));
			$forbidden_var = array('http', '/', '\\');
			foreach($array_var as $var)
			{
				$var = a::get($array_var, $var);
				if(!str::find($forbidden_var, $var)) $name .= '-' .$var;
			}
		}
		
		// Looking for a cached file
		$modified_source = time();
		$extension = $content ? 'json' : 'html';
		$file = self::search($name. '-[0-9]*');
		if($file)
		{
			$modified = explode('-', $file);
			$modified = a::last($modified);
			
			// If source file has been updated
			$modified_source = isset($params['source'])
				? filemtime(a::get($params, 'source'))
				: $modified;
			
			if($modified == $modified_source and (time() - filemtime($file)) <= self::$cache_time) $cached = $file;
			else f::remove($file);
		}
		
		// If no cached file found, we create one
		if(!isset($cached))
			$cached = $name.'-'.$modified_source.'.'.$extension;		
			self::$cached_file = self::$cache_folder.$cached;
			
		// Caching of pregiven data
		if($content or f::extension(self::$cached_file) == 'json')
		{
			if(file_exists(self::$cached_file)) $content = f::read(self::$cached_file, 'json');
			else f::write(self::$cached_file, json_encode($content));
			return $content;
		}
		
		// Caching of the whole page
		else
		{
			if(file_exists(self::$cached_file)) content::load(self::$cached_file);
			else content::start();	
			return file_exists($cached);
		}		
	}

	/**
	 * Saves an output initiated with cache_fetch
	 * 
	 * @return mixed 		Echoes all the data that was just cached
	 */
	static function save()
	{
		if(self::$cached_file)
		{
			$content = content::end(TRUE);
			f::write(self::$cached_file, $content);
			self::$cached_file = NULL;
			echo $content;
		}		
	}

	/**
	 * Search for files inside the cache
	 * 
	 * @param string 		$search The key to look for
	 * @param boolean 	If false returns the first file found, if true returns all files found
	 * @return mixed 		FALSE if the file hasn't been found, the path if it has
	 */
	static function search($search, $all_files = false)
	{
		$file = glob($search.'.{json,html}', GLOB_BRACE);
		if($all_files) return $file;
		return $file ? a::get($file, 0) : FALSE;
	}

	/**
	 * Deletes file(s) from the cache. The key passed can contain * and braces as it's parsed by glob()
	 * 
	 * @param string 		$delete The keys to look for. If NULL, the function empties the cache folder
	 * @param boolean 	$sloppy If true if will look for all files containing the key, if not it will search an exact match
	 * @return boolean 	True if the file(s) have been correctly removed, false if not found
	 */
	static function delete($delete = NULL, $sloppy = FALSE)
	{
		if(!$delete) $delete = '*';			
		
		$files = self::search($delete, true);
		if($files) foreach($files as $file) f::remove($file);
		else return FALSE;
	}
}
?>