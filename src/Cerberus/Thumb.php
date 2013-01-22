<?php
namespace Cerberus;

use \Underscore\Types\String;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Illuminate\Routing\UrlGenerator;

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
   * Creates a square thumbnail of the provided image
   *
   * @param string  $image Path to the image
   * @param integer $size  The thumbnail size
   *
   * @return string  The path to the thumbnail
   */
  public function square($image, $size = 200)
  {
    // Check if the image is in cache
    if ($cached = $this->cacheOfExists($image)) return $cached;

    // Setup Imagine
    $mode  = ImageInterface::THUMBNAIL_OUTBOUND;
    $box   = $this->getSquare($size);
    $cache = $this->getHashOf($image);

    // Generate the thumbnail
    $this->getNewImagine()
      ->open($this->getPathTo($image))
      ->thumbnail($box, $mode)
      ->save($cache);

    return $cache;
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
    if (file_exists($hash)) return $hash;
    return false;
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
   * Get a square box
   *
   * @param integer $size The box size
   *
   * @return Box
   */
  private function getSquare($size)
  {
    return new Box($size, $size);
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
    if (!String::contains($image, 'public')) {
      $image = 'public/'.$image;
    }

    return $this->url->asset($image);
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
