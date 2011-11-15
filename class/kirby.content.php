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
	static function cache($filepath, $basename = NULL, $return = TRUE, $GET = TRUE)
	{
		if(file_exists($filepath))
		{
			if(!$basename)
			{
				// Si mise en cache autorisée
				global $pageVoulue, $sousPageVoulue;
				$basename = $pageVoulue. '-' .$sousPageVoulue;
				$cache = db::field('structure', 'cache', 'CONCAT_WS("-",parent,page) = "' .$basename. '"');
			}
			else $cache = TRUE;
			
			if(config::get('cache', TRUE) == FALSE or !$cache) return $filepath;
			else
			{
				$basename = 'cerberus/cache/' .l::current(). '-' .$basename;
				
				// Variables en cache
				if($GET) $getvar = a::remove($_GET, array('page', 'pageSub', 'PHPSESSID'));
				if(isset($getvar) and !empty($getvar)) $basename .= '-' .simplode('-', '-', $getvar);
				
				// Rercherche d'un fichier en cache
				$found_files = glob($basename.'-[0-9]*.html');
				$modifiedPHP = filemtime($filepath);
				if(isset($found_files[0]))
				{
					$modified = explode('-', $found_files[0]);
					$modified = end($modified);
					
					//$duree_cache = array(1 => 604800, 2 => 604800);
					
					// Si le fichier a été mis à jour on vide le cache
					if($modified == $modifiedPHP and (time() - filemtime($found_files[0])) <= 604800) $cachename = $found_files[0];
					else unlink($found_files[0]);
				}
				if(!isset($cachename))
					$cachename = $basename. '-' .$modifiedPHP. '.html';
	
				if(file_exists($cachename))
				{
					if($return) return $cachename;
					else include $cachename;
				}
				else
				{
					content::start();
					f::inclure($filepath);
					$content = content::end(true);
					
					f::write($cachename, $content);
	
					if($return) return $cachename;
					else echo $content;
				}
			}
		}
		else
		{
			prompt('Le fichier ' .$filepath. ' est introuvable');
			errorHandle('Warning', 'Le fichier ' .$filepath. ' est introuvable', __FILE__, __LINE__);
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