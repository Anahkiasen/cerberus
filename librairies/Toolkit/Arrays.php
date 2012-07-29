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
    if(String::find('.', $key))
      $key = explode('.', $key);

    // If the key is plain, just return the value/fallback
    if(!is_array($key))

      return (isset($array[$key])) ? $array[$key] : $fallback;

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
     * @param  array   $array  The source array
     * @param  mixed   $search The value to look for
     * @return array           The result array without the removed element
   */
  public function removeValue($array, $search)
  {
    return self::remove($array, $search, false);
  }
}