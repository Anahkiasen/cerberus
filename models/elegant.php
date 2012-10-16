<?php
class Elegant extends Eloquent
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
    if(!$lang) $lang = Config::get('application.language');

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
    if(static::$polyglot and static::langValid($key)) return true;

    return parent::__isset($key);
  }

  public function __get($key)
  {
    if(static::$polyglot) {
      if(in_array($key, static::$polyglot)) {
        $lang = Config::get('application.language');
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
    $langs = array_keys($localization[key($localization)]);

    // Build lang arrays
    foreach($localization as $key => $value) {
      foreach($langs as $lang) {
        ${$lang}[$key] = array_get($value, $lang);
        ${$lang}['lang'] = $lang;
      }
    }

    // Update
    foreach($langs as $lang) {
      if($this->$lang) $this->$lang()->update($$lang);
      else $this->$lang()->insert($$lang);
    }
  }

  // Attributes ---------------------------------------------------- /

  public function __toString()
  {
    return (string) $this->name;
  }

  // Helpers ------------------------------------------------------- /

  /**
   * Whether a given language is valid or not
   *
   * @param  string $lang  The language to valid
   * @return boolean       Valid or not
   */
  public static function langValid($lang)
  {
    return in_array($lang, Config::get('application.languages'));
  }

}