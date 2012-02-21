<?php
class f
{
	/*
	########################################
	###### ACTIONS SUR UN FICHIER ##########
	########################################
	*/

	/// Creates a new file
	static function write($file, $content = NULL, $append = false)
	{
		$folder = dirname($file);
		if(!file_exists($folder))
		{
			$dossier = dir::make($dossier);
			if($dossier) self::write($file, $content);
			else str::display(l::get('file.folder.error'), 'error');
		}
		else
		{
			if(is_array($content))
				$content = a::json($content);
		    	$mode = ($append) ? FILE_APPEND : false;
		    	$write = @file_put_contents($file, $content, $mode);
	    
			if(file_exists($file)) @chmod($file, 0666);
	    	return $write;
		}
	}
	
	// Appends new content to an existing file
	static function append($file, $content)
	{
		return self::write($file, $content, true);
	}
	
	// Reads the content of a file
	static function read($file, $parse = false)
	{
		if(file_exists($file))
		{
			$content = @file_get_contents($file);
			return ($parse) ? str::parse($content, $parse) : $content;
		}
		else return false;
	}

	// Moves a file to a new location
	static function move($old, $new)
	{
		if(!file_exists($old)) return false;
		else return (@rename($old, $new) && file_exists($new));
	}

	/// Deletes a file
	static function remove($file)
	{
		if(is_array($file))
			foreach($file as $infile) self::remove($infile);
		else
		{
			if(is_dir($file)) return dir::remove($file);
			else 
				return (file_exists($file) and is_file($file) and !empty($file))
					? @unlink($file)
					: false;
		}
	}

	/*
	########################################
	###### INFORMATIONS SUR FICHIER ########
	########################################
	*/

	//// Returns the path of a file only if it exists
	static function path($file, $default = NULL)
	{
		return (file_exists($file)) ? $file : $default;
	}
	
	// Gets the extension of a file
	static function extension($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	// Extracts the filename from a file path
	static function filename($name)
	{
		return basename($name);
	}

	// Extracts the name from a file path or filename without extension
	static function name($name, $remove_path = false)
	{
		if($remove_path == true)
			$name = self::filename($name);
		
		$dot = strrpos($name,'.');
		if($dot) $name = substr($name, 0, $dot);
		return $name;
	}

	// Just an alternative for dirname() to stay consistent
	static function dirname($file = __FILE__)
	{
		return dirname($file);
	}
	
	// Returns the size of a file.
	static function size($file, $nice = false)
	{
		@clearstatcache();
		$size = @filesize($file);
		if(!$size) return false;
		else return ($nice) ? self::nice_size($size) : $size;
	}

	// Converts an integer size into a human readable format
	static function nice_size($size)
	{
		$size = str::sanitize($size, 'int');
		if($size < 1) return '0 kb';

		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2). ' ' .$unit[$i];
	}
	
	// Convert the filename to a new extension
	static function convert($name, $type = 'jpg')
	{
		return self::name($name).$type;
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

	//// Returns the type of the file
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