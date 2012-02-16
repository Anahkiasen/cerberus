<?php
class f
{
	/*
	########################################
	###### ACTIONS SUR UN FICHIER ##########
	########################################
	*/

	// Écrit dans un fichier
	static function write($file, $content = NULL, $append = false)
	{
		$dossier = dirname($file);
		if(!file_exists($dossier))
		{
			if(!mkdir($dossier, 0700, true))
				str::display(l::get('file.folder.error'), 'error');
			else self::write($file, $content);
		}
		else
		{
			if(is_array($content))
				$content 	= a::json($content);
				$mode		= ($append) ? FILE_APPEND : false;
				$write 		= @file_put_contents($file, $content, $mode);
			
			if($write and file_exists($file))
				@chmod($file, 0666);
			
			return $write;
		}
	}
	
	// Écrit à la fin d'un fichier
	static function append($file, $content)
	{
		return self::write($file, $content, true);
	}
	
	// Lit un fichier
	static function read($file, $parse = false)
	{
		if(file_exists($file))
		{
			$content = @file_get_contents($file);
			return ($parse) ? str::parse($content, $parse) : $content;
		}
		else return false;
	}

	// Déplacer un fichier
	static function move($old, $new)
	{
		if(!file_exists($old)) return false;
		else return (@rename($old, $new) && file_exists($new));
	}

	// Supprimer un fichier
	static function remove($file)
	{
		if(is_array($file))
			foreach($file as $infile) self::remove($infile);
		else
		{
			if(is_dir($file))
				return (file_exists($file))
					? self::remove_folder($file)
					: false;
					
			else
				return (file_exists($file) and is_file($file) and !empty($file))
					? @unlink($file)
					: false;
		}
	}
	
	// Supprime un dossier
	static function remove_folder($file, $empty = false)
	{
		if(substr($file, -1) == "/") $file = substr($file, 0, -1);
		
		if(!file_exists($file) || !is_dir($file)) return false;
		elseif(!is_readable($file)) return false;
		else
		{
			// Lecture du dossier
			$fileHandle = opendir($file);
			while ($contents = readdir($fileHandle))
			{
				if($contents != '.' && $contents != '..')
				{
					$path = $file . "/" . $contents;
					
					// Suppression du fichier/dossier
					if(is_dir($path)) self::remove_folder($path);
					else self::remove($path);
				}
			}
			closedir($fileHandle);
	
			if($empty == false and !rmdir($file)) return false;
			return true;
		}	
	}

	/*
	########################################
	###### INFORMATIONS SUR FICHIER ########
	########################################
	*/

	// Retourne le chemin d'un fichier seulement s'il existe
	static function path($file, $default = NULL)
	{
		return (file_exists($file)) ? $file : $default;
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
	static function name($name, $remove_path = false)
	{
		if($remove_path == true)
			$name = self::filename($name);
		
		$dot = strrpos($name,'.');
		if($dot) $name = substr($name, 0, $dot);
		return $name;
	}

	// Retourne le nom du dossier courant
	static function dirname($file = __FILE__)
	{
		return dirname($file);
	}
	
	// Calcule la taille d'un fichier
	static function size($file, $nice = false)
	{
		@clearstatcache();
		$size = @filesize($file);
		if(!$size) return false;
		else return ($nice) ? self::nice_size($size) : $size;
	}

	// Affiche lisiblement la taille d'un fichier
	static function nice_size($size)
	{
		$size = str::sanitize($size, 'int');
		if($size < 1) return '0 kb';

		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($size/pow(1024,($i = floor(log($size,1024)))),2).' '.$unit[$i];
	}
	
	// Convertit un fichier
	static function convert($name, $type = 'jpg')
	{
		return self::name($name) . $type;
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
			return true;
		}
		else return false;
	}

	// Détecte le type d'un fichier
	static function filecat($extension) 
	{
		$typeArray = array(
			'audio' 		=> array('aac', 'ac3', 'aif', 'aiff', 'm3a', 'm4a', 'm4b', 'mka', 'mp1', 'mp2', 'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma'),
			'video' 		=> array('asf', 'avi', 'divx', 'dv', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt', 'rm', 'vob', 'wmv'),
			'document' 		=> array('doc', 'docx', 'docm', 'dotm', 'odt', 'pages', 'pdf', 'rtf', 'wp', 'wpd'),
			'spreadsheet' 	=> array('numbers', 'ods', 'xls', 'xlsx', 'xlsb', 'xlsm' ),
			'interactive' 	=> array('key', 'ppt', 'pptx', 'pptm', 'odp', 'swf'),
			'text' 			=> array('asc', 'csv', 'tsv', 'txt'),
			'archive' 		=> array('bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip'),
			'code' 			=> array('css', 'htm', 'html', 'php', 'js'),
			'image'			=> array('jpeg', 'jpg', 'png', 'gif'));
			
		foreach($typeArray as $type => $exts)
			if(in_array($extension, $exts)) return $type;
	}
}
?>