<?php
namespace Cerberus\Toolkit;

use Cerberus\Toolkit\Arrays as a,
    Cerberus\Toolkit\File   as f,
    Cerberus\Toolkit\String as str;

class Directory
{
  /**
   * Creates a new directory.
   * If the folders containing the end folder don't exist, they will be created too
   *
   * @param   string  $directory The path for the new directory
   * @param   boolean $recursive Tells the function to act recursively or not
   * @return  boolean True: the dir has been created, false: creating failed
   * @package Kirby, Cerberus
   */
  public static function make($directory, $chmod = 0755, $recursive = true)
  {
    // Recursive creation -------------------------------------- /

    if ($recursive) {
      // Explode
      $directories  = self::explode($directory);
      $current_path = null;

      // Crawl through directories and create all nonexistants
      foreach($directories as $directory)
        if ($directory !== '.' and $directory !== '..') {
          // Update current position
          $current_path .= $directory.'/';

          // Create folder if they don't exist
          $make = self::make($current_path, $chmod, $recursive = false);

          // Escape if an error is thrown
          if(!$make) return false;
        }

      return true;
    }

    // Create a folder ----------------------------------------- /

    // If it already exists, cancel
    if(is_dir($directory)) return true;

    // Try creating the folder
    try {
      $created = mkdir($directory, $chmod);
      chmod($directory, $chmod);
    } catch (Exception $e) { Debug::handle($e); }

    return true;
  }

  /**
   * Reads all files from a directory and returns them as an array.
   * It skips unwanted invisible stuff.
   *
   * @param   string  $dir The path of directory
   * @return  mixed   An array of filenames or false
   */
  public static function read($dir)
  {
    if(!is_dir($dir)) return false;

    // Ignore common system files
    $skip = array('.', '..', '.DS_Store');

    // Return list of files
    return array_diff(scandir($dir), $skip);
  }

  /**
   * Reads a directory and returns a full set of info about it
   *
   * @param   string  $dir The path of directory
   * @return  mixed   An info array or false
   */
  public static function inspect($dir)
  {
    if(!is_dir($dir)) return array();

    // Fill basic informations for folder
    $data = array(
      'name'     => basename($dir),
      'root'     => $dir,
      'modified' => filemtime($dir),
      'files'    => array(),
      'children' => array());

    // List all files
    $files = self::read($dir);
    foreach ($files as $file) {
      if(is_dir($dir.'/'.$file)) $data['children'][] = $file;
      else $data['files'][] = $file;
    }

    return $data;
  }

  /**
   * Moves a directory to a new location
   *
   * @param   string  $old The old name of the file
   * @param   string  $new The new name of the file
   * @return  boolean Whether the directory has been renamed or not
   */
  public static function rename($old, $new)
  {
    if(!is_dir($old)) return false;

    return (@rename($old, $new) and is_dir($new));
  }

  /**
   * Moves a directory to a new location
   *
   * @param   string  $old The current path of the directory
   * @param   string  $new The desired path where the dir should be moved to
   * @return  boolean True: the directory has been moved, false: moving failed
   */
  public static function move($old, $new)
  {
    if(!is_dir($old) or !is_dir($new)) return false;

    $oldMove = explode('/', $old);
    $oldMove = end($oldMove);

    // Calculate new place for the old folder
    $newPlace = $new.'/'.$oldMove;

    return (rename($old, $newPlace) and is_dir($newPlace));
  }

  /**
   * Deletes a directory
   *
   * @param   string   $dir The path of the directory
   * @param   boolean  $keep If set to true, the directory will flushed but not removed.
   * @return  boolean  True: the directory has been removed, false: removing failed
   */
  public static function remove($dir, $keep = false)
  {
    if(!is_dir($dir)) return false;

    $handle = @opendir($dir);
    $skip = array('.', '..');

    if(!$handle) return false;

    while ($item = @readdir($handle)) {
      if(is_dir($dir.'/'.$item) and !in_array($item, $skip))
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
  public static function clean($dir)
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
  public static function size($path, $nice = false, $recursive = true)
  {
    // If unexisting file, return false
    if(!file_exists($path)) return false;

    // If it's a file, return it size
    elseif(file_exists($path) and !is_dir($path)) return f::size($path);

    // Else, if it's a directory
    $size = 0;

    foreach (glob($path."/*") as $file) {
      if ($file != "." and $file != "..") {
        $size += is_dir($file)
          ? $recursive
            ? self::size($file, $nice, true)
            : 0
          : f::size($file);
      }
    }

    return ($nice) ? f::nice_size($size) : $size;
  }

  /**
   * Recursively check when the dir and all
   * subfolders have been modified for the last time.
   *
   * @param  string $dir      The path of the directory
   * @param  int    $modified internal modified store
   * @return int
   */
  public static function modified($dir, $modified = 0)
  {
    $files = self::read($dir);
    foreach ($files as $file) {
      if(!is_dir($dir.'/'.$file)) continue;

      $filectime = filemtime($dir.'/'.$file);
      $modified = ($filectime > $modified) ? $filectime : $modified;
      $modified = self::modified($dir.'/'.$file, $modified);
    }

    return $modified;
  }

  /**
   * Returns the last folder of a path
   *
   * @param  string $folder A filepath
   * @return string A folder name
   */
  public static function last($folder)
  {
    return self::nth($folder, 0);
  }

  /**
   * Returns the nth folder of a path
   *
   * @param  string $folder A filepath
   * @param  int    $nth    The nth folder to return, starting from the end
   *
   * @return string A folder name
   */
  public static function nth($folder, $nth)
  {
    // Explode list of folders
    $folders = explode('/', $folder);
    if(sizeof($folders) == 1) $folders = explode("\\", $folder);

    // Calculate array size
    $size = sizeof($folders);

    // Remove last element if it's a file
    $last = end($folders);
    if (empty($last) or str::find('.', $last)) {
      $folders = a::remove($folders, ($size - 1));
      $size--;
    }

    // Get folder
    if($nth == 0) $last = end($folders);
    else $last = a::get($folders, ($size - 1 - $nth));
    return $last;
  }

  /**
   * Explodes a filepath with correct separator
   *
   * @param  string $filepath A file path
   * @return array            An exploded filepath
   */
  public static function explode($filepath)
  {
    $folders = explode('/', $filepath);
    if(sizeof($folders) == 1)
      $folders = explode('\\', $filepath);

    return $folders;
  }
}
