<?php
class dir
{
	// Creates a new directory
	static function make($dir)
	{
		if(is_dir($dir)) return true;
		if(!@mkdir($dir, 0755)) return false;
		@chmod($dir, 0755);
		return true;
	}

	// Reads all files from a directory and returns them as an array. It skips unwanted invisible stuff. 
	static function read($dir)
	{
		if(!is_dir($dir)) return false;
		$skip = array('.', '..', '.DS_Store');
		return array_diff(scandir($dir), $skip);
	}

	// Reads a directory and returns a full set of info about it
	static function inspect($dir)
	{
		if(!is_dir($dir)) return array();

		$files = dir::read($dir);
		$modified = filemtime($dir);
		$data = array(
			'name' => basename($dir),
			'root' => $dir,
			'modified' => $modified,
			'files' => array(),
			'children' => array());

		foreach($files AS $file)
		{
			if(is_dir($dir.'/'.$file)) $data['children'][] = $file;
			else $data['files'][] = $file;
		}

		return $data;
	}

	// Moves a directory to a new location
	static function move($old, $new)
	{
		if(!is_dir($old)) return false;
		return (@rename($old, $new) && is_dir($new));
	}

	// Deletes a directory
	static function remove($dir, $keep = false)
	{
		if(!is_dir($dir)) return false;

		$handle = @opendir($dir);
		$skip = array('.', '..');

		if(!$handle) return false;

		while($item = @readdir($handle))
		{
			if(is_dir($dir.'/'.$item) && !in_array($item, $skip))
				self::remove($dir.'/'.$item);
			
			else if(!in_array($item, $skip))
				@unlink($dir.'/'.$item);
		}

		@closedir($handle);
		if(!$keep) return @rmdir($dir);
		return true;
	}

	// Flushes a directory
	static function clean($dir)
	{
		return self::remove($dir, true);
	}

	// Gets the size of the directory and all subfolders and files
	static function size($path, $recursive = true, $nice = false)
	{
		if(!file_exists($path)) return false;
		if(is_file($path)) return self::size($path, $nice);
		$size = 0;
		
		foreach(glob($path."/*") AS $file)
		{
			if($file != "." && $file != "..")
			{
				$size += $recursive				
					? self::size($file, true)
					: f::size($path);
			}
		}
		return ($nice) ? f::nice_size($size) : $size;
	}

	// Recursively check when the dir and all subfolders have been modified for the last time. 
	static function modified($dir, $modified = 0)
	{
		$files = self::read($dir);
		foreach($files AS $file)
		{
			if(!is_dir($dir.'/'.$file)) continue;
			
			$filectime = filemtime($dir.'/'.$file);
			$modified = ($filectime > $modified) ? $filectime : $modified;
			$modified = self::modified($dir.'/'.$file, $modified);
		}
		return $modified;
	}
}
?>