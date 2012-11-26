<?php
/**
 *
 * File
 *
 * This class makes it easy to
 * create/edit/delete files
 */
namespace Cerberus;

use \Input;
use \Underscore\Arrays;

class File extends \Laravel\File
{

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ACTIONS //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Creates a new file, and the folders containing it if they don't exist
   *
   * @param  string  $file    The path for the new file
   * @param  mixed   $content Either a string or an array. Arrays will be converted to JSON.
   * @param  boolean $append  true: append the content to an exisiting file if available. false: overwrite.
   * @return boolean
   */
  public static function write($file, $content, $append = false)
  {
    // Get the files'folder
    $folder = dirname($file);

    // If it doesn't exist, try to create it
    if (!file_exists($folder)) Directory::create($folder);

    // Transform array to JSON if necessary
    if(is_array($content)) $content = Arrays::json($content);

    return \File::put($file, $content);
  }

  /**
   * Deletes one or more files
   *
   * @param  mixed   $file The path for the file or an array of path
   * @return boolean
   */
  public static function remove()
  {
    // Get files to remove
    $files = func_get_args();

    // If we passed an array of files
    if(sizeof($files) == 1 and is_array($files[0])) $files = $files[0];

    // Remove each file
    $return = 0;
    foreach ($files as $file) {
      $remove = static::delete($file);
      if($remove) $return++;
    }

    return sizeof($files) == $return;
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// INFORMATIONS ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Extracts the filename from a file path
   *
   * @param  string $file The path
   * @return string       The file name only
   */
  public static function filename($name)
  {
    return basename($name);
  }

  /**
   * Extracts the core name from a path/filename
   *
   * @param  string  $file       The path or filename
   * @param  boolean $removePath Remove the path from the name
   * @return string
   */
  public static function name($name, $removePath = true)
  {
    if($removePath)
      $name = self::filename($name);

    $dot = strrpos($name,'.');
    if($dot) $name = substr($name, 0, $dot);

    return $name;
  }

  /**
   * Gets a file's extension
   *
   * @param  string $filename A filename
   * @return string           An extension
   */
  public static function extension($filename)
  {
    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    if (!$extension) {
      $extension = substr($filename, strpos('.', $filename));
    }

    return $extension;
  }

  /**
   * Sanitizes a file's name while preserving its extension
   *
   * @param  string $filename The filename
   * @return string           Sanitized filename
   */
  public static function sanitize($filename)
  {
    // Get extension
    $extension = self::extension($filename);

    // Remove extension, sanitize name, put back extension
    $filename  = String::remove($extension, $filename);
    $filename  = String::slugify($filename, '-');
    $filename .= '.' . $extension;

    return $filename;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// UTILITIES ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the results from a multiple files upload, and rearrange it
   *
   * @param  string $field The field name
   * @return array        An array of files
   */
  public static function getMultipleFiles($field)
  {
    // Iterate over the files
    $_files = Input::file($field);

    // Abort if no files
    if(!$_files) return false;

    // Recreate the files array
    foreach ($_files as $column => $keys) {
      foreach ($keys as $key => $value) {
        $files[$key][$column] = $value;
      }
    }

    return $files;
  }
}
