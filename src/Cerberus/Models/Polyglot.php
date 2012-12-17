<?php
namespace Cerberus\Models;

use \Config;
use \Cerberus\Language;

class Polyglot extends Elegant
{
  public static $polyglot = false;

  // Localization -------------------------------------------------- /

  /**
   * Reroutes functions to the language in use
   *
   * @param  string  $lang A language to use
   * @return Has_One
   */
  public function lang($lang = null)
  {
    if(!$lang) $lang = Language::current();

    return $this->$lang();
  }

  public function fr()
  {
    return $this->has_one(get_called_class().'Lang')->where_lang('fr');
  }

  public function en()
  {
    return $this->has_one(get_called_class().'Lang')->where_lang('en');
  }

  public function __isset($key)
  {
    if(static::$polyglot and Language::valid($key)) return true;

    return parent::__isset($key);
  }

  public function __get($key)
  {
    if (static::$polyglot) {
      if (in_array($key, static::$polyglot)) {
        return $this->lang ? $this->lang->$key : null;
      }
    }

    return parent::__get($key);
  }

  // Functions ----------------------------------------------------- /

  /**
   * Localize a model with an array of lang arrays
   *
   * @param  array $localization An array in the form [field][lang][value]
   */
  public function localize($localization)
  {
    if(!$localization) return false;

    $langs = array_keys($localization[key($localization)]);

    // Build lang arrays
    foreach ($localization as $key => $value) {
      foreach ($langs as $lang) {
        ${$lang}[$key] = array_get($value, $lang);
        ${$lang}['lang'] = $lang;
      }
    }

    // Update
    foreach ($langs as $lang) {
      if($this->$lang) $this->$lang()->update($$lang);
      else $this->$lang()->insert($$lang);
    }
  }

}
