<?php
/**
 *
 * Thumb
 *
 * Thumb generation and caching
 * Based on IMWG
 */
namespace Cerberus;

use \Closure;
use \Config;
use \Imwg;

class Thumb
{
  /**
   * The folder where thumbs reside
   * @var string
   */
  public static $folder = 'cache/';

  /**
   * Cache a remote image
   *
   * @param  string $image The image to cache
   * @return string A path to the cached image
   */
  public static function cacheRemote($image, $filename = null)
  {
    if(!$image) return $image;

    // Create filename
    $path = 'public/';
    $path .= $filename
      ? static::hash($filename.'.'.File::extension($image), false)
      : static::hash($image);

    // Put remote image in cache if we haven't already
    if (!file_exists($path)) {
      File::put($path, file_get_contents($image));

      // Convert to JPG
      if (String::contains($path, 'png')) {
        $jpg = str_replace('png', 'jpg', $path);
        Imwg::open($path)->convert('JPG')->save($jpg);
        File::remove($path);
        $path = $jpg;
      }
    }

    return String::remove('public/', $path);
  }

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
    } else $image = static::cacheRemote($image);

    // Generate hash
    $thumb  = md5($image.$width.$height);
    $thumb .= '.'.File::extension($image);

    // Square by default
    if(!$height) $height = $width;

    // Thumb generation
    Imwg::open($image)
      ->resize($width, $height, 'crop')
      ->save('public/' .static::$folder.$thumb, 75);

    return static::$folder.$thumb;
  }

  /**
   * Whether a thumb for a given image exists
   *
   * @param  string $thumb The image to check for
   * @return boolean
   */
  public static function exists($thumb)
  {
    if (!String::contains($thumb, '.')) $thumb .= '.jpg';
    $thumb = File::sanitize($thumb);
    $thumb = static::$folder.$thumb;

    return file_exists(path('public').$thumb) ? $thumb : false;
  }

  // Helpers ------------------------------------------------------- /

  /**
   * Get the path to an image
   *
   * @param string $image The image
   */
  private static function path($image)
  {
    $path = Config::get('imwg.image_path', Config::get('imwg::settings.image_path', path('public')));

    return rtrim($path, '/').'/'.$image;
  }

  /**
   * Generates a cache hash from an Image object
   *
   * @param array $image An image generated
   * @return string A cache hash
   */
  private static function hash($image, $crypt = true)
  {
    $hash  = static::$folder;

    // If the image is remote, put it in a separate folder
    if (String::contains($image, 'http')) {
      $hash .= 'remote/';
    }

    // Crypt the name if asked
    $hash .= $crypt
      ? md5($image).'.'.File::extension($image)
      : File::sanitize($image);

    return $hash;
  }
}
