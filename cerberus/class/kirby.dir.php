<?php
class dir
{
  /**
   * Creates a new directory. 
   * If the folders containing the end folder don't exist, they will be created too
   * [CERBERUS-EDIT]
   * 
   * @param   string  $directory The path for the new directory
   * @param   boolean $recursive Tells the function to act recursively or not
   * @return  boolean True: the dir has been created, false: creating failed
   */
	static function make($directory, $recursive = TRUE)
	{
		if(!$recursive)
		{
			if(is_dir($directory)) return true;
			if(!@mkdir($directory, 0755)) return false;
			@chmod($directory, 0755);
			return true;	
		}
		else
		{
			$directories = explode('/', $directory);
			$current_path = NULL;
			
			foreach($directories as $directory)
				if($directory !== '.' and $directory !== '..')
				{
					$current_path .= $directory.'/';
					$make = self::make($current_path, FALSE);
					if(!$make) return false;	
				}
			return true;
		}
	}

  /**
   * Reads all files from a directory and returns them as an array. 
   * It skips unwanted invisible stuff. 
   * 
   * @param   string  $dir The path of directory
   * @return  mixed   An array of filenames or false
   */
	static function read($dir)
	{
		if(!is_dir($dir)) return false;
		$skip = array('.', '..', '.DS_Store');
		return array_diff(scandir($dir), $skip);
	}

  /**
   * Reads a directory and returns a full set of info about it
   * 
   * @param   string  $dir The path of directory
   * @return  mixed   An info array or false
   */  
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

  /**
   * Moves a directory to a new location
   * 
   * @param   string  $old The current path of the directory
   * @param   string  $new The desired path where the dir should be moved to
   * @return  boolean True: the directory has been moved, false: moving failed
   */  
	static function move($old, $new)
	{
		if(!is_dir($old)) return false;
		return (@rename($old, $new) && is_dir($new));
	}

  /**
   * Deletes a directory
   * 
   * @param   string   $dir The path of the directory
   * @param   boolean  $keep If set to true, the directory will flushed but not removed. 
   * @return  boolean  True: the directory has been removed, false: removing failed
   */  
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

  /**
   * Flushes a directory
   * 
   * @param   string   $dir The path of the directory
   * @return  boolean  True: the directory has been flushed, false: flushing failed
   */  
	static function clean($dir)
	{
		return self::remove($dir, true);
	}

  /**
   * Gets the size of the directory and all subfolders and files
   * 
   * @param   string   $dir The path of the directory
   * @param   boolean  $recursive 
   * @param   boolean  $nice returns the size in a human readable size 
   * @return  mixed  
   */  
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

  /**
   * Recursively check when the dir and all 
   * subfolders have been modified for the last time. 
   * 
   * @param   string   $dir The path of the directory
   * @param   int      $modified internal modified store 
   * @return  int  
   */ 
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