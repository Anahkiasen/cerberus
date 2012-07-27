<?php
/**
 *
 * File
 *
 * This class makes it easy to
 * create/edit/delete files
 */
namespace Cerberus\Toolkit;

use Cerberus\Toolkit\String;

class File extends \File
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
    if (!file_exists($folder)) {
      try {
        Directory::make($folder);
      } catch (Exception $e) {
        var_dump($e);
      }
    }

    // Define content, file and mode
    if(is_array($content))
      $content = Arrays::json($content);
      $mode    = ($append) ? LOCK_EX | FILE_APPEND : LOCK_EX;
      $write   = file_put_contents($file, $content, $mode);

    // If we had no content to put and the file is empty, then OK
    if($content == null and $write == 0)
      $write = true;

    // If the file was created, set permissions
    if(file_exists($file))
      chmod($file, 0666);

    return $write;
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
    $file = func_get_args();

    // If the file is alone, unarray it
    if(sizeof($file) == 1)
      $file = Arrays::get($file, 0);

    // If we have an array, recursively call itself
    if (is_array($file)) {
      // Remove each files and check it all went well
      $return = 0;
      foreach ($file as $f) {
        $remove = self::remove($f);
        if($remove) $return++;
      }

      // Return the number of success / number of files
      return $return == sizeof($file);
    }

    // Remove a file
    return (file_exists($file) and is_file($file) and !empty($file))
      ? @unlink($file)
      : false;
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
    return pathinfo($filename, PATHINFO_EXTENSION);
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
    $filename  = String::slugify($filename);
    $filename .= '.' . $extension;

    return $filename;
  }
}
