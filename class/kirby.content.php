<?php
class content
{
	static private $cachename = NULL;

	// Démarre la récupération du contenu
	static function start()
	{
		ob_start();
	}

	// Retourne le contenu récupéré
	static function end($return = FALSE)
	{
		if($return)
		{
			$content = ob_get_contents();
			$content = trim(preg_replace('/\s+/', ' ', $content));
			ob_end_clean();
			return $content;
		}
		ob_end_flush();
	}
			
	// Mise en cache de la page
	static function cache_start($basename)
	{
		global $switcher;
		$CORE = $basename;
		$cache = db::field('structure', 'cache', 'CONCAT_WS("-",parent,page) = "' .$basename. '"');

		if($cache and !LOCAL)
		{			
			// Variables en cache
			if($switcher) $basename = $switcher->current(). '-' .$basename;
			$basename = 'cerberus/cache/' .l::current(). '-' .$basename;
			$getvar = a::remove($_GET, array('page', 'pageSub', 'PHPSESSID', 'langue'));
			if(isset($getvar) and !empty($getvar)) $basename .= '-' .a::simplode('-', '-', $getvar);
			
			// Date de modification du fichier de base
			$page = f::sexist('pages/' .$CORE. '.php');
			if(!$page) $page = f::sexist('pages/' .$CORE. '.html');
			$modifiedPHP = ($page) ? filemtime($page) : time(); 
			
			// Rercherche d'un fichier en cache
			$found_files = glob($basename.'-[0-9]*.html');
			if(isset($found_files[0]))
			{
				$modified = explode('-', $found_files[0]);
				$modified = end($modified);
				
				// Si le fichier a été mis à jour on vide le cache
				if($modified == $modifiedPHP and (time() - filemtime($found_files[0])) <= 604800) $cachename = $found_files[0];
				else unlink($found_files[0]);
			}
			if(!isset($cachename))
				$cachename = $basename. '-' .$modifiedPHP. '.html';

			if(file_exists($cachename))
			{
				f::inclure($cachename);
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

	// Affichage de la page
	static function cache_end()
	{
		if(self::$cachename)
		{
			$OB = self::end(TRUE);
			f::write(self::$cachename, $OB);
			echo $OB;
		}
	}
		
	// Détermine le type du fichier
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
			'gif'	=> 'image/gif'
		);

		$ctype	 = a::get($args, 0, c::get('content_type', 'text/html'));
		$ctype	 = a::get($ctypes, $ctype, $ctype);

		$charset = a::get($args, 1, c::get('charset', 'utf-8'));
		header('Content-type: ' . $ctype . '; ' . $charset);
	}

}
?>