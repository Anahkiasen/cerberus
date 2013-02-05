<?php
namespace Cerberus\Modules;

class Resize
{
  /**
   * The current image
   * @var image
   */
  private $image      = null;

  /**
   * The current image's type
   * @var string
   */
  private $image_type = null;

  /**
   * Default image compression
   * @var integer
   */
  private $default_compression = 75;

  public function __construct($filename, $save = null, $width = null, $height = null, $scale = null, $compression = null)
  {
    if ($filename) {
      // Set compression
      if(!$compression) $compression = $this->default_compression;

      // Load image
      $this->load($filename);

      // Resize image
      if($scale) $this->scale($scale);
      if ($width or $height) {
        if($width and $height) $this->resize($width, $height);
        elseif($width and !$height) $this->resizeToWidth($width);
        else $this->resizeToHeight($height);
      }

      // Save it
      if($save) $this->save($save);
      else $this->output();
    }
  }

  ///////////////////////////////////////////////////////////////////
  ///////////////////////// CREATE IMAGE ////////////////////////////
  ///////////////////////////////////////////////////////////////////

  public function load($filename)
  {
    // Get image type
    $image_info = getimagesize($filename);
    $this->image_type = $image_info[2];

    // Create picture
    switch ($this->image_type) {
      case IMAGETYPE_JPEG:
        $this->image = imagecreatefromjpeg($filename);
        break;

      case IMAGETYPE_GIF:
        $this->image = imagecreatefromgif($filename);
        break;

      case IMAGETYPE_PNG:
        $this->image = imagecreatefrompng($filename);
        break;
    }
  }

  public function save($filename, $image_type = IMAGETYPE_JPEG, $compression = null, $permissions = null)
  {
    if(!$compression) $compression = $this->default_compression;

    switch ($image_type) {
      case IMAGETYPE_JPEG:
        imagejpeg($this->image, $filename, $compression);
        break;

      case IMAGETYPE_GIF:
        imagegif($this->image, $filename);
        break;

      case IMAGETYPE_PNG:
        imagepng($this->image, $filename);
        break;
    }

    if($permissions)
      chmod($filename, $permissions);
  }

  private function output($image_type = IMAGETYPE_JPEG)
  {
    switch ($image_type) {
      case IMAGETYPE_JPEG:
        imagejpeg($this->image);
        break;

      case IMAGETYPE_GIF:
        imagegif($this->image);
        break;

      case IMAGETYPE_PNG:
        imagepng($this->image);
        break;
    }
  }

  ///////////////////////////////////////////////////////////////////
  ////////////////////////////// RESIZE /////////////////////////////
  ///////////////////////////////////////////////////////////////////

  public function resizeToHeight($height)
  {
    $ratio = $height / $this->getHeight();
    $width = $this->getWidth() * $ratio;
    $this->resize($width,$height);
  }

  public function resizeToWidth($width)
  {
    $ratio  = $width / $this->getWidth();
    $height = $this->getheight() * $ratio;
    $this->resize($width,$height);
  }

  public function scale($scale)
  {
    $width  = $this->getWidth()  * $scale / 100;
    $height = $this->getheight() * $scale / 100;
    $this->resize($width,$height);
  }

  public function resize($width,$height)
  {
    $new_image = imagecreatetruecolor($width, $height);
    imagecopyresampled($new_image, $this->image, 0, 0, 0, 0, $width, $height, $this->getWidth(), $this->getHeight());
    $this->image = $new_image;
  }

  ///////////////////////////////////////////////////////////////////
  ////////////////////////////// HELPERS ////////////////////////////
  ///////////////////////////////////////////////////////////////////

  private function getWidth()
  {
    return imagesx($this->image);
  }

  private function getHeight()
  {
    return imagesy($this->image);
  }
}
