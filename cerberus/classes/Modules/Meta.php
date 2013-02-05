<?php
/**
 *
 * meta
 *
 * This class handles the gathering and managing of meta tags
 *
 * @package Cerberus
 */
namespace Cerberus\Modules;

use Cerberus\Core\Config,
    Cerberus\Core\Head,
    Cerberus\Core\Navigation,
    Cerberus\Toolkit\Arrays   as a,
    Cerberus\Toolkit\Cache,
    Cerberus\Toolkit\Database as db,
    Cerberus\Toolkit\Language as l,
    Cerberus\Toolkit\Request  as r,
    Cerberus\Toolkit\String   as str;

class Meta
{
  /**
   * Main data array
   * @var array
   */
  public static $meta       = null;

  /**
   * The path to the cached meta data array
   * @var string
   */
  private static $file      = null;

  /**
   * A list of overwrites to apply to the main array
   * @var array
   */
  private static $overwrite = array();

  //////////////////////////////////////////////////////////////////
  ///////////////////////// INITIALIZATION /////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Fetch the meta data either from cache or from a database
   * If no cached file found, then create it
   */
  public static function build()
  {
    // Check if the necessary tables exist
    $dbExist = SQL ? db::is_table(array('cerberus_meta', 'cerberus_structure')) : false;

    // Get the cached meta data file
    self::$file = PATH_CACHE. 'meta-' .l::current(). '.json';
    $meta = cache::fetch('meta');
    if($meta) self::$meta = $meta;

    // If no cached meta array was found, let's create it
    elseif (!$meta and (config::get('meta') or $dbExist) and SQL) {
      // If the tables don't exist we create them
      if (!$dbExist) {
        update::table('cerberus_meta');
        update::table('cerberus_structure');
      }

      // Gathering of the metadata
      $metadata = db::left_join(
        'cerberus_meta M',
        'cerberus_structure S',
        'M.page = S.id',
        'S.page, S.parent, M.title, M.description, M.url',
        array('langue' => l::current()));

      // Little magic applied to the data
      foreach ($metadata as $values) {
        $title = a::get($values, 'title');
        $page = empty($values['parent']) ? $values['page'] : $values['parent'].'-'.$values['page'];

        // If no title
        if(empty($title)) $values['title'] = head::getTitle($values['parent'], $values['page']);

        // If no description found, use title instead
        if(empty($values['description'])) $values['description'] = $title;

        // If no keywords, use words from description
        if(empty($values['keywords'])) $values['keywords'] = self::keywords($values['description']);

        // If no page name found, use title also
        if(empty($values['url'])) $values['url'] = str::slugify($title);

        // Insert into main array
        $variables = array('title', 'description', 'keywords', 'url');
        foreach($variables as $v)
          self::$meta[$page][$v] = a::get($values, $v);
      }
    }

    // If REALLY no data to use, GOD HELP US ALL
    else self::$meta = array();
  }

  //////////////////////////////////////////////////////////////////
  ///////////////////////////// METHODS ////////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Overwrite a particular meta tag
   *
   * @param string  $key    The key to overwrite
   * @param string  $value  The new content of the string
   */
  public static function set($key, $value = null)
  {
    self::$overwrite[$key] = $value;
  }

  /**
   * Get a particular key from the array
   * @param  string  $get      The key to get
   * @param  string  $default  A fallback if the key wasn't found
   * @return string           The wanted value
   */
  public static function get($get = null, $default = null)
  {
    // Get current page
    $current = navigation::current();

    // If no key specified, return the whole array
    if(!$get) return self::$meta;

    // Little magic applied to the title
    if ($get == 'title') {
      // Getting the page name
      $page = head::getTitle();

      // Fetch the page description in the meta array
      $pageDescription = a::get(self::$meta, $current.',title');

      if($page and $pageDescription)      $title = $page. ' - ' .$pageDescription;
      elseif(!$page and $pageDescription) $title = $pageDescription;
      else                                $title = $page;

      self::$meta[$current]['title'] = $title;
    }

    return (isset(self::$meta[$current][$get]) and !empty(self::$meta[$current][$get]))
      ? ucfirst(str::accents(a::get(self::$meta[$current], $get, $default)))
      : $default;
  }

  /**
   * Get the meta data for a specific page (defaults to the current one)
   *
   * @param  string  $page  The page wanted
   * @param  string  $key   A specific key to return instead of the whole thing
   * @return array   The meta data of said page
   */
  public static function page($page = null, $key = null)
  {
    // If the meta array doesn't exist yet, let's build it
    if(!is_array(self::$meta)) self::build();

    // If no page specified, defaults to the current one
    if(!$page) $page = navigation::current();

    // If we have the data, return it
    if(isset(self::$meta[$page]) and !empty(self::$meta[$page]))

      return $key
        ? a::get(self::$meta, $page.','.$key)
        : a::get(self::$meta, $page);

    return false;
  }

  /**
   * Generate a cloud of keywords from a string
   *
   * @param  string  $string  The string to use as base
   * @return string  A shuffled string of keywords
   */
  public static function keywords($string)
  {
    // Remove special characters
    $string = preg_replace('#([,\.\r\n\-])#', null, $string);

    // Separate each word
    $string = explode(' ', $string);

    // Shuffle and unique the array
    shuffle($string);
    $string = array_filter(array_unique($string));

    // Implode as keywords
    $string = implode(', ', $string);

    return $string;
  }

  //////////////////////////////////////////////////////////////////
  ///////////////////////////// EXPORT /////////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
   * Format the meta data as meta tags for the <head>
   */
  public static function head()
  {
    // If the meta array doesn't exist yet, let's build it
    if(!is_array(self::$meta)) self::build();

    // Get the current page info
    $meta = self::page();
    if (!$meta) {
      if(navigation::isCurrent('admin') and r::get('admin'))
        $meta['title'] = 'Administration - Gestion ' .ucfirst(r::get('admin'));
      else return false;
    }

    // Treat the data a little
    foreach ($meta as $key => $value) {
      if(!$value or $key == 'url') continue; // If empty tag

      // Send the data to core.head
      if ($key == 'title') {
        // Take into account the overwrites
        if(str::find('{meta}', $value))
          $value = str_replace('{meta}', $value, a::get(self::$overwrite, $key, '{meta}'));

        else $value = '{title} - ' .$value;

        head::title($value);
      } else head::set('meta', array('name' => $key, 'content' => $value));
    }

    // Cache the modified data
    cache::fetch('meta', self::$meta);
  }
}
