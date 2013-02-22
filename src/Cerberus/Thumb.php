<?php
namespace Cerberus;

use App;
use Illuminate\Routing\UrlGenerator;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Underscore\Types\String;

class Thumb
{
  /**
   * The current image engine
   * @var string
   */
  private $engine = 'Gd';

  /**
   * Path to the generated images folder
   * @var string
   */
  private $cache = 'cache/';

  /**
   * Build a new instance of Thumb
   *
   * @param UrlGenerator $url
   */
  public function __construct(UrlGenerator $url)
  {
    $this->url = $url;
  }

  /**
   * Create a cropped thumb
   *
   * @param string  $image  Path to the image
   * @param integer $width
   * @param integer $height
   *
   * @return string
   */
  public function create($image, $width = 200, $height = 200)
  {
    // Check if the image is in cache
    if ($cached = $this->cacheOfExists($image)) return $cached;

    // Setup Imagine
    $mode  = ImageInterface::THUMBNAIL_OUTBOUND;
    $box   = new Box($width, $height);
    $cache = $this->getHashOf($image);

    $path = $this->getPathTo($image);
    if (!file_exists($path)) return false;

    // Generate the thumbnail
    $this->getNewImagine()
      ->open($path)
      ->thumbnail($box, $mode)
      ->save($cache);

    return $cache;
  }

  /**
   * Creates a square thumbnail of the provided image
   *
   * @param string  $image Path to the image
   * @param integer $size  The thumbnail size
   *
   * @return string  The path to the thumbnail
   */
  public function square($image, $size = 200)
  {
    return $this->create($image, $size, $size);
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// CORE METHODS ///////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Check if a given image was already processed and is in the cache
   *
   * @param string $image The image path
   *
   * @return boolean
   */
  private function cacheOfExists($image)
  {
    $hash = $this->getHashOf($image);

    return file_exists($hash) ? $hash : false;
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Generate a cache hash for an image
   *
   * @param string $image The image name
   *
   * @return string The cache hash
   */
  private function getHashOf($image)
  {
    return $this->cache.md5($image).'.jpg';
  }

  /**
   * Get the path to an image
   *
   * @param  string $image The image
   * @return string Its path
   */
  private function getPathTo($image)
  {
    // Account for rewritten URLs
    if (String::contains($image, 'public')) {
      $image = String::sliceFrom($image, 'public');
      $image = String::remove($image, 'public/');
    }

    return App::make('path.public').'/'.$image;
  }

  /**
   * Get a new instance of Image with the selected engine
   *
   * @return Imagine
   */
  private function getNewImagine()
  {
    $imagine = '\Imagine\\'.$this->engine.'\Imagine';

    return new $imagine;
  }
}
