<?php
/**
 *
 * Directory
 *
 * This class provides helpers to manipulate
 * and gather informations on directories
 */
namespace Cerberus;

use \Underscore\Types\Arrays;

class Directory
{

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// PATHS ////////////////////////////////
  ////////////////////////////////////////////////////////////////////

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

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ALIASES //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Recursive version of mkdir
   */
  public static function create($directory, $recursive = true)
  {
    // Explode each directory of the path and create them
    if ($recursive) {
      $path = null;
      $directories = self::explode($directory);
      foreach ($directories as $directory) {
        $path .= $directory.'/';
        static::create($path, false);
      }
    }

    // Cancel if invalid path
    if(is_dir($directory) or !$directory) return true;

    return \File::mkdir($directory);
  }

  /**
   * Alias for mvdir
   */
  public static function move($from, $to)
  {
    return \File::mvdir($from, $to);
  }

  /**
   * Alias for rmdir
   */
  public static function remove($directory)
  {
    return \File::rmdir($directory);
  }

  /**
   * Alias for cleandir
   */
  public static function flush($directory)
  {
    return \File::cleandir($directory);
  }

}
