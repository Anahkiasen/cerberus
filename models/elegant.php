<?php
class Elegant extends Eloquent
{
  public static $polyglot = false;
  public $includes = array('lang');

  // Localization -------------------------------------------------- /

  public function lang($lang = null)
  {
    if(!$lang) $lang = Config::get('application.language');

    return $this->has_one(get_called_class().'Lang')->where_lang($lang);
  }

  public function __isset($key)
  {
    if(static::$polyglot and static::langValid($key)) return true;

    return parent::__isset($key);
  }

  public function __get($key)
  {
    if(static::$polyglot) {
      if(static::langValid($key)) return $this->lang($key)->first();
      if(in_array($key, static::$polyglot)) {
        $lang = $this->lang;
        return $lang ? $lang->$key : null;
      }
    }

    return parent::__get($key);
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