<?php
/**
 *
 * Arrays
 *
 * This class provides helpers to manipulate
 * and gather informations on arrays
 */

namespace Cerberus\Toolkit;

use Cerberus\Toolkit\String;

class Arrays
{
  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// ACTIONS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Gets an element of an array by key
   *
   * @param  array $array    The source array
   * @param  mixed $key      The key to look for, or a path through a
   *                         multidimensionnal array under the form
   *                         key1.key2... or array[key1,key2,...]
   * @param  mixed $fallback Optional default value, which will be returned if no element has been found
   * @return mixed           The wanted key
   */
  public static function get($array, $key, $fallback = null)
  {
    // If the key is an array of keys
    if(String::find('.', $key)) {
      $key = explode('.', $key);
    }

    // If the key is plain, just return the value/fallback
    if(!is_array($key)) {
      return (isset($array[$key])) ? $array[$key] : $fallback;
    }

    // Else crawl the array for the right key
    foreach ($key as $k) {
      $array = self::get($array, $k, $fallback);
      if($array == $fallback) break;
    }

    return $array;
  }

  /**
   * Removes an element by key or value
   *
   * @param  array   $array  The source array
   * @param  mixed   $search The value or key to look for
   * @param  boolean $key    Pass true to search for an key, pass false to search for an value.
   * @return array           The result array without the removed element
   */
  public static function remove($array, $search, $key = true)
  {
    // If we are looking for serveral keys/values
    if (is_array($search)) {
      foreach($search as $s)
        $array = self::remove($array, $s, $key);

      return $array;
    }

    // If it's a key, plainly use unset
    if($key) unset($array[$search]);

    // If it's a value, use array_search
    else {
      $found_all = false;
      while (!$found_all) {
        $index = array_search($search, $array);
        if($index !== false) unset($array[$index]);
        else $found_all = true;
      }
    }

    return $array;
  }

  /**
   * Removes an element by value
   *
   * @param  array $array  The source array
   * @param  mixed $search The value to look for
   * @return array         The result array without the removed element
   */
  public static function removeValue($array, $search)
  {
    return self::remove($array, $search, false);
  }

  /**
   * Extracts a single column from an array
   *
   * @param  array  $array The source array
   * @param  string $key   The key name of the column to extract
   * @return array         The result array with all values from that column.
   */
  public static function pluck($array, $key)
  {
    return array_map(function($v) use ($key) {
      return is_object($v) ? $v->$key : $v[$key];
    }, $array);
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////// INFORMATIONS ////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Returns the average value of an array
   *
   * @param  array   $array    The source array
   * @param  integer $decimals The number of decimals to return
   * @return integer           The average value
   */
  public static function average($array, $decimals = 0)
  {
    return round((array_sum($array) / sizeof($array)), $decimals);
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////// MULTIDIMENSIONNAL ARRAYS /////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
    * Sorts a multi-dimensional array by a certain column
    *
    * @param  array   $array     The source array
    * @param  string  $field     The name of the column
    * @param  string  $direction desc (descending) or asc (ascending)
    * @param  const   $method    A PHP sort method flag.
    * @return array              The sorted array
    */
  public static function sort($array, $field, $direction = 'desc', $method = SORT_REGULAR)
  {
  	// Make sur the passed argument is an array
  	if(!is_array($array)) return $array;

  	// Get correct PHP constant for direction
    $direction = (strtolower($direction) == 'desc') ? SORT_DESC : SORT_ASC;

    // Create
    $helper = array();
    foreach($array as $key => $row) {
      $helper[$key] = (is_object($row))
        ? (method_exists($row, $field))
          ? String::lower($row -> $field())
          : String::lower($row -> $field)
        : (isset($row[$field]))
          ? String::lower($row[$field])
          : $row;
    }

    array_multisort($helper, $direction, $method, $array);

    return $array;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////// EXPORT / IMPORT /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
    * Converts an array to CSV format
    *
    * @param  array   $array         The source array
    * @param  string  $delimiter     The delimiter between fields, default ;
    * @param  boolean $exportHeaders Whether headers should be included in the table
    * @return string                 The CSV string
    */
  public static function toCsv($array, $delimiter = ';', $exportHeaders = false)
  {
    $csv = null;

    // Fetch headers if requested
    if($exportHeaders) {
      $headers = array_keys(self::first($array));
      $csv .= implode($delimiter, $headers);
    }

    foreach ($array as $row) {
      // Add line break if we're not on the first row
      if(!empty($csv)) $csv .= PHP_EOL;

      // Quote values and create row
      foreach($row as $key => $value)
        $row[$key] = '"' .stripslashes($value). '"';
        $csv .= implode($delimiter, $row);
    }

    return $csv;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ALIASES //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Returns the first element of an array
   *
   * @param  array $array The source array
   * @return mixed        The first element
   */
  public static function first($array)
  {
    return array_shift($array);
  }
}
