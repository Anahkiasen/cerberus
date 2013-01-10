<?php
namespace Cerberus;

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
    // Setup Imagine
    $mode  = ImageInterface::THUMBNAIL_OUTBOUND;
    $box   = $this->getSquare($size);
    $cache = $this->getHashOf($image);

    // Generate the thumbnail
    $thumb = $this->getNewImagine()
      ->open($this->url->asset($image))
      ->thumbnail($box, $mode)
      ->save($cache);

    return $cache;
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