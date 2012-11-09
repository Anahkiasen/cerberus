<?php
/**
 *
 * Thumb
 *
 * Thumb generation and caching
 * Based on IMWG
 */
namespace Cerberus;

use \Config;
use \Imwg;
use \Closure;

class Thumb
{
  public static function closure($from, Closure $closure)
  {
    // Generates a hash for this image
    $image = static::path($from);
    $hash  = static::hash($from);

    // Process the image
    $image = Imwg::open($image);
    $image = $closure($image)->save(path('public').$hash);

    return HTML::image($hash);
  }

  /**
   * Generates a thumb with Resizer and cache it
   *
   * @param  string  $image  The image path
   * @param  integer $width  The desired width
   * @param  integer $height The desired height (defaults to width)
   * @return string          An image path to the thumb
   */
  public static function create($image, $width = 200, $height = null)
  {
    // Basic checks
    if (!str_contains($image, 'http')) {
      $image = static::path($image);
      if(!file_exists($image)) return $image;
    }

    // Square by default
    if(!$height) $height = $width;

    // Generate hash
    $thumb = md5($image.$width.$height);
    $thumb .= '.'.File::extension($image);

    // Thumb generation
    Imwg::open($image)
      ->resize($width, $height, 'crop')
      ->save('public/cache/'.$thumb, 75);

    return 'cache/'.$thumb;
  }

  /**
   * Get the path to an image
   *
   * @param string $image The image
   */
  private static function path($image)
  {
    $path = Config::get('imwg::settings.image_path', path('public'));

    return $path.'/'.$image;
  }

  /**
   * Generates a cache hash from an Image object
   *
   * @param array $image An image generated
   * @return string A cache hash
   */
  private static function hash($image)
  {
    $hash  = 'cache/';
    $hash .= md5($image).'.'.File::extension($image);

    return $hash;
  }
}
