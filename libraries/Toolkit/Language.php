<?php
/**
 *
 * Language
 *
 * Language helpers
 */

namespace Cerberus\Toolkit;

use \URL;

class Language
{
  /**
   * Returns the current language being used
   *
   * @return string A language index
   */
  public static function current()
  {
    return \Config::get('application.language');
  }

  /**
   * Get the URL to switch language, keeping the current page or not
   *
   * @param  string  $lang  The new language
   * @param  boolean $reset Whether navigation should be reset
   * @return string         An URL
   */
  public static function to($lang, $reset = false)
  {
    if($reset) return URL::base().'/'.$lang;

    return str_replace(
      URL::base(),
      URL::base().'/'.$lang,
      URL::current());
  }

  /**
   * Sets the locale according to the current language
   *
   * @param  string $language A language string to use
   * @return
   */
  public static function locale($language = false)
  {
    // If nothing was given, just use current language
    if(!$language) $language = self::current();

    // Base table of languages
    $locales = array(
      'de' => array('de_DE.UTF8','de_DE@euro','de_DE','de','ge'),
      'fr' => array('fr_FR.UTF8','fr_FR','fr'),
      'es' => array('es_ES.UTF8','es_ES','es'),
      'it' => array('it_IT.UTF8','it_IT','it'),
      'pt' => array('pt_PT.UTF8','pt_PT','pt'),
      'zh' => array('zh_CN.UTF8','zh_CN','zh'),
      'en' => array('en_US.UTF8','en_US','en'),
    );

    // Set new locale
    setlocale(LC_ALL, Arrays::get($locales, $language, array('en_US.UTF8','en_US','en')));

    return setlocale(LC_ALL, 0);
  }

  /**
   * Apply the correct language constraint to an array of eager load relationships
   *
   * @return array An array of relationships
   */
  public static function eager()
  {
    $language = static::current();
    $relationships = array();

    foreach (func_get_args() as $r) {
      if (String::find('lang', $r)) {
        $relationships[$r] = function($query) use ($language) {
          $query->where_lang($language);
        };
      } else {
        $relationships[] = $r;
      }
    }

    return $relationships;
  }

  /**
   * Flattens out all language string in the current language for easier export
   *
   * @return array A flattened lang array
   */
  public static function compile($output = null)
  {
    $files = glob(path('app').'language/' .static::current(). '/*');

    // Fetch the content of all the language files
    foreach($files as $file) {
      $file = File::name($file);
      if($file == 'validation') {
        $lang[$file] = \Lang::line($file.'.custom')->get();
        $lang[$file] = \Lang::line($file.'.attributes')->get();
      } else $lang[$file] = \Lang::line($file)->get();
    }

    // If the website isn't localized, cancel
    if(!isset($lang)) return false;

    // Flatten the final array$return = array();
    $lang = Arrays::flatten($lang);

    // Sort the array
    ksort($lang);

    // If we provided an output file, save to it
    if($output) {
      $lang = Arrays::toCsv($lang);
      \File::put(path('storage').'work'.DS.$output, $lang);
    }

    return $lang;
  }
}
