<?php
/**
 *
 * Laravel
 *
 * A suite of helpers for Laravel
 */
namespace Cerberus;

use Resizer;

class Laravel
{
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
    // Basic checks
    if (!str_contains($image, 'http')) {
      if(!starts_with($image, 'public/')) $image = 'public/'.$image;
      if(!file_exists($image)) return $image;
    }

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
