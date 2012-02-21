<?php
class content
{
	static private $cachename = NULL;

	/*
	########################################
	########## FONCTIONS CACHE #############
	########################################
	*/
			
	//// Mise en cache de la page
	static function cache_start($params)
	{
		global $switcher;
		
		$basename = $CORE = $params['basename'];
		if($params['cachetime'] == 0) $params['cachetime'] = 60 * 60 * 24 * 365;
		
		$cache = (SQL and db::is_table('cerberus_structure')) ? db::field('cerberus_structure', 'cache', 'CONCAT_WS("-",parent,page) = "' .$basename. '"') : $params['cache'];
		if(navigation::$page) == 'admin') $cache = FALSE;
		
		if($params['cache'] or ($cache and CACHE))
		{			
			// Variables en cache
			if($switcher) $basename = $switcher->current(). '-' .$basename;
			if($params['getvar']) $basename = l::current(). '-' .$basename;
			$basename = 'cerberus/cache/' .$basename;
			
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

	//// Affiche et sauvegarde le cache
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
	
	//// Vide le cache
	static function uncache($page = NULL)
	{
		if($page == 'meta') $captcha ='{meta-*,lang-*}';
		else $captcha = ($page)
			? '*-' .$page. '-*.html'
			: '*.*';

		foreach(glob('cerberus/cache/' .$captcha, GLOB_BRACE) as $file)
			f::remove($file);	
	}
	
	/*
	########################################
	############ MOTEUER CACHE #############
	########################################
	*/
	
	// Starts the output buffer
	static function start()
	{
		ob_start();
	}

	// Stops the output buffer and flush the content or return it.
	static function end($return = FALSE)
	{
		if($return)
		{
			$content = ob_get_contents();
			ob_end_clean();
			$content = str::accents($content);
			return $content;
		}
		ob_end_flush();
	}
	
	// Loads content from a passed file
	static function load($file, $return = true)
	{
		self::start();
		require_once ($file);
		$content = self::end(true);
		if($return)
			return $content;
		echo $content;
	}
		
	// Simplifies setting content type headers
	static function type()
	{
		$args = func_get_args();

		// Raccourics content_type
		$ctypes	= array(
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
		header('Content-type: ' .$ctype. '; charset=' .$charset);
	}
}
?>