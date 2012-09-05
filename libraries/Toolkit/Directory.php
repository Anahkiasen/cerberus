<?php
/**
 *
 * Directory
 *
 * This class provides helpers to manipulate
 * and gather informations on directories
 */
namespace Cerberus\Toolkit;

use Cerberus\Toolkit\String,
    Cerberus\Toolkit\Arrays;

class Directory
{
  ////////////////////////////////////////////////////////////////////
  //////////////////////////// ACTIONS ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

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

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// PATHS ////////////////////////////////
  ////////////////////////////////////////////////////////////////////

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
    if (empty($last) or String::find('.', $last)) {
      $folders = Arrays::remove($folders, ($size - 1));
      $size--;
    }

    // Get folder
    if($nth == 0) $last = end($folders);
    else $last = Arrays::get($folders, ($size - 1 - $nth));
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
