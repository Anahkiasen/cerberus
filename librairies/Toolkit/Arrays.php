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
   * @param  array    $array    The source array
   * @param  mixed    $key      The key to look for, or a path through a
   *                            multidimensionnal array under the form
   *                            key1.key2... or array[key1,key2,...]
   * @param  mixed    $fallback Optional default value, which will be returned if no element has been found
   * @return mixed
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
    foreach($key as $k)
    {
      $array = self::get($array, $k, $fallback);
      if($array == $fallback) break;
    }
    return $array;
  }
}
