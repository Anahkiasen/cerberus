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
   * @return string           A translated string
   */
  public static function translate($key, $fallback = null)
  {
    if (!$fallback) $fallback = $key;

    // Search for the key itself
    $translation = Lang::line($key)->get(null, '');

    // If not found, search in the field attributes
    if (!$translation) {
      $translation =
        Lang::line('validation.attributes.'.$key)->get(null,
        $fallback);
    }

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
    $title = Lang::line($title, null)->get();

    Section::inject('title', $title);
  }
}
