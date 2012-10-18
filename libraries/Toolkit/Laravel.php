<?php
/**
 *
 * Laravel
 *
 * A suite of helpers for Laravel
 */

namespace Cerberus\Toolkit;

use Lang;
use Resizer;
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

  /**
   * Generates a thumb with Resizer and cache it
   *
   * @param  string  $image  The image path
   * @param  integer $width  The desired width
   * @param  integer $height The desired height (defaults to width)
   * @return string          An image path to the thumb
   */
  public static function thumb($image, $width = 200, $height = null)
  {
    $image = 'public/'.$image;
    if(!file_exists($image)) return $image;

    // Square by default
    if(!$height) $height = $width;

    // Thumb generation
    $thumb = 'cache/'.md5($image.$width.$height).'.jpg';
    if (!file_exists('public/'.$thumb)) {
      Resizer::open($image)
        ->resize($width, $height, 'crop')
        ->save('public/'.$thumb, 75);
    }

    return $thumb;
  }
}
