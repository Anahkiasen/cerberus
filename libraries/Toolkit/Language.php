<?php
/**
 *
 * Language
 *
 * Language helpers
 */

namespace Cerberus\Toolkit;

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
   * Get the URL to switch language, keeping the current page
   *
   * @param  string  $lang  The new lang
   * @param  boolean $reset Whether navigation should be reset
   * @return string         An URL
   */
  public static function url($lang, $reset = false)
  {
    if($reset) return \URL::base().'/'.$lang;

    $url = str_replace(\URL::base(), null, \URL::current());
    $url = \URL::base().'/'.$lang.$url;

    return $url;
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
    $relationships = array();
    foreach(func_get_args() as $r) {
      if(String::find('lang', $r)) {
        $relationships[$r] = function($query) {
          $query->where_lang(static::current());
        };
      } else {
        $relationships[] = $r;
      }
    }

    return $relationships;
  }
}
