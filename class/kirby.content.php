<?php
class content
{
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
			ob_end_clean();
			return $content;
		}
		ob_end_flush();
	}
	
	// Met en cache un ficheir ou l'inclut s'il existe
	static function cache($filepath, $basename = NULL, $return = FALSE)
	{
		if(config::get('cache', TRUE) == FALSE or LOCAL) return $filepath;
		else
		{
			global $pageVoulue, $sousPageVoulue;
							
			if(!$basename) $basename = $pageVoulue. '-' .$sousPageVoulue;
			$basename = 'cerberus/cache/' .l::current(). '-' .$basename;
			
			// Variables en cache
			$getvar = a::remove($_GET, array('page', 'pageSub', 'PHPSESSID'));
			if(!empty($getvar)) $basename .= '-' .simplode('-', '-', $getvar);

			// Rercherche d'un fichier en cache
			$found_files = glob($basename.'-[0-9]*.html');
			$modifiedPHP = filemtime($filepath);
			if(isset($found_files[0]))
			{
				$modified = explode('-', $found_files[0]);
				$modified = end($modified);
				
				// Si le fichier a été mis à jour on vide le cache
				if($modified == $modifiedPHP and (time() - filemtime($found_files[0])) <= 86400) $cachename = $found_files[0];
				else unlink($found_files[0]);
			}
			if(!isset($cachename))
				$cachename = $basename. '-' .$modifiedPHP. '.html';

			if(file_exists($cachename)) return $cachename;
			else
			{
				content::start();
				f::inclure($filepath);
				$content = content::end(true);
				
				f::write($cachename, $content);

				if($return == FALSE) echo $content;
				else return $cachename;
			}
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