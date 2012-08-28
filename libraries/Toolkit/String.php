<?php
/**
 *
 * String
 *
 * This class provides various helpers
 * to manipulate and gather informations about strings
 */

namespace Cerberus\Toolkit;

class String extends \Str
{
  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// CREATE ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Accord a string with a number
   *
   * @param  integer $count A number
   * @param  string  $many  If many
   * @param  string  $one   If one
   * @param  string  $zero  If one
   * @return string         A string
   */
  public static function accord($count, $many, $one, $zero = null)
  {
    if($count == 1) return $one;
    else if($count == 0 and !empty($zero)) return $zero;
    else return $many;
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// ACTIONS //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Removes a part of a string
   * @param  string $delete The part of the string to remove
   * @param  string $string The string to correct
   * @return string         The corrected string
   */
  public static function remove($delete, $string)
  {
    // If we only have one string to remove
    if(!is_array($delete)) $string = str_replace($delete, null, $string);

    // Else, use Regex
    else $string =  preg_replace('#(' .implode('|', $delete). ')#', null, $string);

    // Trim and return
    return trim($string);
  }

  /**
   * Toggles a string between two states
   *
   * @param  string  $string      The string to toggle
   * @param  string  $firstValue  First value
   * @param  string  $secondValue Second value
   * @param  boolean $loose       Whether a string neither matching 1 or 2 should be changed
   * @return string               The toggled string
   */
  public static function toggle($string, $firstValue, $secondValue, $loose = false)
  {
    if(!$loose and !in_array($string, array($firstValue, $secondValue)))
      return $string;

    return $string == $firstValue ? $secondValue : $firstValue;
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// INFORMATIONS //////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Find one or more needles in one or more haystacks
   *
   * Also avoid the retarded counter-intuitive original
   * strpos syntax that makes you put haystack before needle
   *
   * @param  mixed   $needle        The needle(s) to search for
   * @param  mixed   $haystack      The haystack(s) to search in
   * @param  boolean $caseSensitive Whether the function is case sensitive or not
   * @param  boolean $absolute      Whether all needle need to be found or whether one is enough
   * @return boolean Found or not
   */
  public static function find($needle, $haystack, $caseSensitive = false, $absolute = false)
  {
    // If several needles
    if (is_array($needle) or is_array($haystack)) {
      if (is_array($needle)) {
        $from = $needle;
        $to   = $haystack;
      } else {
        $from = $haystack;
        $to   = $needle;
      }
      $found = 0;
      foreach($from as $need)
        if(self::find($need, $to, $absolute, $caseSensitive))
          $found++;

      return ($absolute) ? count($from) == $found : $found > 0;
    } else {
      // If not case sensitive
      if (!$caseSensitive) {
        $haystack = strtolower($haystack);
        $needle   = strtolower($needle);
      }

      // If string found
      $pos = strpos($haystack, $needle);

      return !($pos === false);
    }
  }

  /**
   * Determine if a given string begins with a given value.
   *
   * @param  string  $haystack The string to look in
   * @param  string  $needle   The string to look for
   * @return boolean
   */
  public static function startsWith($haystack, $needle)
  {
    return strpos($haystack, $needle) === 0;
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// ALIASES ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Alias of String::slug
   */
  public static function slugify($string, $separator = '-')
  {
    return self::slug($string, $separator);
  }
}