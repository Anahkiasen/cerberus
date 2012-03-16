<?php
/**
 * 
 * Content
 * 
 * This class handles output buffering,
 * content loading and setting content type headers. 
 * 
 * @package Kirby
 */
class content
{
	private static $cache_folder = 'cerberus/cache/';
	private static $cachename = NULL;
	
	/**
		* Starts the output buffer
		* 
		*/
	static function start()
	{
		ob_start();
	}

	/**
		* Stops the output buffer
		* and flush the content or return it.
		* 
		* @param	boolean	$return Pass true to return the content instead of flushing it 
		* @return mixed
		*/
	static function end($return = false)
	{
		if($return)
		{
			$content = ob_get_contents();
			ob_end_clean();
			return $content;
		}
		ob_end_flush();
	}

	/**
		* Loads content from a passed file
		* 
		* @param	string	$file The path to the file
		* @param	boolean $return True: return the content of the file, false: echo the content
		* @return mixed
		*/
	static function load($file, $return = true)
	{
		self::start();
		require_once($file);
		$content = self::end(true);
		if($return) return $content;
		echo $content;				
	}

	/**
		* Simplifies setting content type headers
		* 
		* @param	string	$ctype The shortcut for the content type. See the keys of the $ctypes array for all available shortcuts
		* @param	string	$charset The charset definition for the content type header. Default is "utf-8"
		*/
	static function type()
	{
		$args = func_get_args();

		// shortcuts for content types
		$ctypes = array(
			'html' => 'text/html',
			'css'	=> 'text/css',
			'js'	 => 'text/javascript',
			'jpg'	=> 'image/jpeg',
			'png'	=> 'image/png',
			'gif'	=> 'image/gif',
			'json' => 'application/json'
		);

		$ctype	 = a::get($args, 0, c::get('content_type', 'text/html'));
		$ctype	 = a::get($ctypes, $ctype, $ctype);
		$charset = a::get($args, 1, c::get('charset', 'utf-8'));

		header('Content-type: ' . $ctype . '; charset=' . $charset);

	}

	/**** Mise en cache de la page */
	static function cache_start($params)
	{
		global $switcher;
		
		$basename = $CORE = $params['basename'];
		if($params['cachetime'] == 0) $params['cachetime'] = config::get('cachetime', 60 * 60 * 24 * 365);
		
		$cache_allowed = (SQL and db::is_table('cerberus_structure'))
			? db::field('cerberus_structure', 'cache', 'CONCAT_WS("-",parent,page) = "' .$basename. '" OR parent = "' .$basename. '"')
			: $params['cache'];
		if(navigation::$page == 'admin') $cache = FALSE;
		
		if($params['cache'] !== false and $cache_allowed and CACHE)
		{			
			// Variables en cache
			if($switcher) $basename = $switcher->current(). '-' .$basename;
			if($params['getvar']) $basename = l::current(). '-' .$basename;
			$basename = self::$cache_folder.$basename;
			
			if($params['getvar'])
			{
				$getvar = a::remove($_GET, array('page', 'pageSub', 'PHPSESSID', 'langue', 'gclid', 'cerberus_debug'));
				foreach($getvar as $key => $value) if(str::find('http://', $value)) $getvar = a::remove($getvar, $key); // Sécurité f::write
				if(isset($getvar) and !empty($getvar)) $basename .= '-' .a::simplode('-', '-', $getvar);
			}
			
			// Date de modification du fichier de base
			$page = f::path('pages/' .$CORE. '.php');
			if(!$page) $page = f::path('pages/' .$CORE. '.html');
			$modifiedPHP = ($page) ? filemtime($page) : time(); 
			
			// Rercherche d'un fichier en cache
			$found_files = glob($basename.'-[0-9]*.html');
			if(isset($found_files[0]))
			{
				$modified = explode('-', $found_files[0]);
				$modified = end($modified);
				
				// Si le fichier a été mis à jour on vide le cache
				if($modified == $modifiedPHP and (time() - filemtime($found_files[0])) <= $params['cachetime']) $cachename = $found_files[0];
				else f::remove($found_files[0]);
			}
			if(!isset($cachename))
				$cachename = $basename. '-' .$modifiedPHP. '.html';
			
			if(file_exists($cachename))
			{
				self::start();
				f::inclure($cachename);
				self::end();
				return false;
			}
			else
			{
				self::start();
				self::$cachename = $cachename;
				return true;
			}
		}
		else return true;
	}

	/**** Affiche et sauvegarde le cache */
	static function cache_end()
	{
		if(self::$cachename)
		{
			$OB = self::end(TRUE);
			f::write(self::$cachename, $OB);
			echo $OB;
		}
		if(config::get('timer', false)) timer::get();
	}
	
}
?>