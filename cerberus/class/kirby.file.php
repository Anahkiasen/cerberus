<?php
/**
 * 
 * File
 * 
 * This class makes it easy to 
 * create/edit/delete files
 * 
 * @package Kirby
 */
class f
{
	/*
	########################################
	###### ACTIONS SUR UN FICHIER ##########
	########################################
	*/

	/**
	 * Creates a new file, and the folders containing it if they don't exist
	 * [CERBERUS-EDIT]
	 *  
	 * @param	string	$file The path for the new file
	 * @param	mixed	 $content Either a string or an array. Arrays will be converted to JSON. 
	 * @param	boolean $append true: append the content to an exisiting file if available. false: overwrite. 
	 * @return boolean 
	 */	
	static function write($file, $content = NULL, $append = false)
	{
		$folder = dirname($file);
		if(!file_exists($folder))
		{
			$folder = dir::make($folder);
			if($folder) self::write($file, $content);
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
	
	/**
	 * Renames a file without moving it
	 * 
	 * f::rename('path/folder/file.php', 'renamed') will per example execute
	 * rename('path/folder/file.php', 'path/folder/renamed.php')
	 * 
	 * @param string     $old The old file _with_ its path
	 * @param string     $new The new name for the file, and new extension if wanted (if not, old extension will be used)
	 * @return boolean   Returns whether renaming the file succeeded or not
	 */
	static function rename($old, $new)
	{
		$old_name = self::filename($old);
		$path = str_replace($old_name, NULL, $old);
		$new = $path.$new;
		if(!str::find('.', $new)) $new .= '.'.self::extension($old_name);
		
		return rename($old, $new);
	}
	
	/**
	 * Appends new content to an existing file
	 * 
	 * @param	string	$file The path for the file
	 * @param	mixed	 $content Either a string or an array. Arrays will be converted to JSON. 
	 * @return boolean 
	 */ 
	static function append($file, $content)
	{
		return self::write($file, $content, true);
	}
	
	/**
	 * Reads the content of a file
	 * 
	 * @param	string	$file The path for the file
	 * @param	mixed	 $parse if set to true, parse the result with the passed method. See: "str::parse()" for more info about available methods. 
	 * @return mixed 
	 */	
	static function read($file, $parse = false)
	{
		if(!file_exists($file)) return false;
		$content = @file_get_contents($file);
		return ($parse) ? str::parse($content, $parse) : $content;
	}

	/**
	 * Moves a file to a new location
	 * 
	 * @param	string	$old The current path for the file
	 * @param	string	$new The path to the new location
	 * @return boolean 
	 */	
	static function move($old, $new)
	{
		if(!file_exists($old)) return false;
		else return (@rename($old, $new) && file_exists($new));
	}

	/**
	 * Gets the extension of a file
	 * [CERBERUS-EDIT]
	 * 
	 * @param	string	$file The filename or path
	 * @return string 
	 */ 
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

	/**
	 * Returns the path of a file if it exists, or a default given value
	 * [CERBERUS-ADD]
	 * 
	 * @param string 	$file The file wanted
	 * @param string 	$default The path to returns if the file doesn't exist
	 * @return string A file path/file name
	 * 
	 */
	static function path($file, $default = NULL)
	{
		return (file_exists($file)) ? $file : $default;
	}
	
	/* Gets the extension of a file */
	static function extension($filename)
	{
		return pathinfo($filename, PATHINFO_EXTENSION);
	}
	
	/**
	 * Extracts the filename from a file path
	 * 
	 * @param	string	$file The path
	 * @return string 
	 */	
	static function filename($name)
	{
		return basename($name);
	}

	/**
	 * Extracts the name from a file path or filename without extension
	 * 
	 * @param	string	$file The path or filename
	 * @param	boolean $remove_path remove the path from the name
	 * @return string 
	 */	
	static function name($name, $remove_path = false)
	{
		if($remove_path == true)
			$name = self::filename($name);
		
		$dot = strrpos($name,'.');
		if($dot) $name = substr($name, 0, $dot);
		return $name;
	}

	/**
	 * Sanitize a filename to strip unwanted special characters
	 * 
	 * @param	string $string The file name
	 * @return string
	 */		
	static function safe_name($string)
	{
		return str::urlify($string);
	}

	/**
	 * Just an alternative for dirname() to stay consistent
	 * 
	 * @param	string	$file The path
	 * @return string 
	 */	
	static function dirname($file = __FILE__)
	{
		return dirname($file);
	}
	
	/**
	 * Returns the size of a file.
	 * 
	 * @param	string	$file The path
	 * @param	boolean $nice True: return the size in a human readable format
	 * @return mixed
	 */	
	static function size($file, $nice = false)
	{
		@clearstatcache();
		$size = @filesize($file);
		if(!$size) return false;
		else return ($nice) ? self::nice_size($size) : $size;
	}

	/**
	 * Converts an integer size into a human readable format
	 * 
	 * @param	int $size The file size
	 * @return string
	 */	 
	static function nice_size($size)
	{
		$size = str::sanitize($size, 'int');
		if($size < 1) return '0 kb';

		$unit = array('b','kb','mb','gb','tb','pb');
		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2). ' ' .$unit[$i];
	}
	
	/**
	 * Convert the filename to a new extension
	 * 
	 * @param	string $name The file name
	 * @param	string $type The new extension
	 * @return string
	 */		
	static function convert($name, $type = 'jpg')
	{
		return self::name($name).$type;
	}
	
	/* Inclure un fichier */
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

	/**
	 * Returns the type of a file according to its extension
	 * [CERBERUS-ADD]
	 * 
	 * @param string 	$file The file to analyze
	 * @return string The filetype
	 */
	static function type($file) 
	{
		if(str::find('.', $file)) $file = self::extension($file);
	
		$types = array(
			'audio'		 => array('aac', 'ac3', 'aif', 'aiff', 'm3a', 'm4a', 'm4b', 'mka', 'mp1', 'mp2', 'mp3', 'ogg', 'oga', 'ram', 'wav', 'wma'),
			'video'		 => array('asf', 'avi', 'divx', 'dv', 'flv', 'm4v', 'mkv', 'mov', 'mp4', 'mpeg', 'mpg', 'mpv', 'ogm', 'ogv', 'qt', 'rm', 'vob', 'wmv'),
			'document'		 => array('doc', 'docx', 'docm', 'dotm', 'odt', 'pages', 'pdf', 'rtf', 'wp', 'wpd'),
			'spreadsheet'	 => array('numbers', 'ods', 'xls', 'xlsx', 'xlsb', 'xlsm' ),
			'interactive'	 => array('key', 'ppt', 'pptx', 'pptm', 'odp', 'swf'),
			'text'			 => array('asc', 'csv', 'tsv', 'txt'),
			'archive'		 => array('bz2', 'cab', 'dmg', 'gz', 'rar', 'sea', 'sit', 'sqx', 'tar', 'tgz', 'zip'),
			'code'			 => array('css', 'htm', 'html', 'php', 'js'),
			'image'			=> array('jpeg', 'jpg', 'png', 'gif'));
			
		foreach($types as $type => $exts)
			if(in_array($file, $exts)) return $type;
	}
}
?>