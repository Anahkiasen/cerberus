<?php
class f
{
	// Écrit dans un fichier
	static function write($file, $content, $append = false)
	{
		$dossier = dirname($file);
		if(!file_exists($dossier))
		{
			if(!mkdir($dossier, 0700, true))
				echo display('Impossible de créer le dossier');
			else self::write($file, $content);
		}
		else
		{
			if(is_array($content))
				$content 	= a::json($content);
				$mode		= ($append) ? FILE_APPEND : false;
				$write 		= @file_put_contents($file, $content, $mode);
			
			@chmod($file, 0777);
			return $write;
		}
	}
	
	// Écrit à la fin d'un fichier
	function append($file, $content)
	{
		return self::write($file, $content, true);
	}
	
	// Lit un fichier
	function read($file, $parse = false)
	{
		$content = @file_get_contents($file);
		return ($parse) ? str::parse($content, $parse) : $content;
	}

	// Déplacer un fichier
	function move($old, $new)
	{
		if(!file_exists($old)) return false;
		else return (@rename($old, $new) && file_exists($new));
	}

	// Supprimer un fichier
	function remove($file)
	{
		return (file_exists($file) && is_file($file) && !empty($file)) ? @unlink($file) : false;
	}

	// Récupère l'extension d'un fichier
	static function extension($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	// Retourne le nom d'un fichier sans le chemin
	static function filename($name)
	{
		return basename($name);
	}

	// Retourne le nom d'un fichier sans l'extension ni le chemin
	function name($name, $remove_path = false)
	{
		if($remove_path == true) $name = self::filename($name);
		else
		{
			$dot = strrpos($name,'.');
			if($dot) $name = substr($name, 0, $dot);
			return $name;
		}
	}

	// Retourne le nom du dossier courant
	function dirname($file = __FILE__)
	{
		return dirname($file);
	}
	
	// Calcule la taille d'un fichier
	function size($file, $nice = false)
	{
		@clearstatcache();
		$size = @filesize($file);
		if(!$size) return false;
		else return ($nice) ? self::nice_size($size) : $size;
	}

	// Affiche lisiblement la taille d'un fichier
	function nice_size($size)
	{
		$size = str::sanitize($size, 'int');
		if($size < 1) return '0 kb';

		$unit=array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
	}
	
	// Convertit un fichier
	function convert($name, $type = 'jpg')
	{
		return self::name($name) . $type;
	}

	// Retourne un nom de fichier sûr
	function safe_name($string)
	{
		return str::urlify($string);
	}
	
	// Inclure un fichier
	static function inclure($file, $type = 'once')
	{
		if(file_exists($file))
		{
			switch($type)
			{
				case 'once':
					include_once $file;
					break;
					
				default:
					include $file;
					break;
			}
		}
	}
}
?>