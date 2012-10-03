<?php
/**
 *
 * Laravel
 *
 * A suite of helpers for Laravel
 */

namespace Cerberus\Toolkit;

use Lang;
use Section;

class Laravel
{
  /**
   * Translates a string with fallbacks
   *
   * @param  string $key      The key/string to translate
   * @param  string $fallback A fallback to display
   * @param  string $lookup   Where in the language files should Cerberus look
   * @return string           A translated string
   */
  public static function translate($key, $fallback = null, $lookup = 'validation.attributes.')
  {
    if(!$fallback) $fallback = $key;

    // Search for the key itself
    $translation = Lang::line($key)->get(null, '');

    // If not found, search in the field attributes
    if(!$translation) $translation =
      Lang::line($lookup.$key)->get(null,
      $fallback);

    return ucfirst($translation);
  }

  /**
   * Attempts to translate a title
   *
   * @param  string $title A page or a title
   * @return string        A title section
   */
  public static function title($title = null)
  {
    $title = static::translate($title, null, $title.'.login');

    Section::inject('title', $title);
  }
}
