<?php
namespace Cerberus;

use \Meido\HTML\HTML as MeidoHTML;

class HTML extends MeidoHTML
{
  /**
   * Generates an image wrapped in a link
   *
   * @param string $url        The url of the link
   * @param string $image      The image path
   * @param string $alt        Image alt text
   * @param array  $attributes The image attributes
   *
   * @return string An image tag in a link
   */
  public function imageLink($url, $image, $alt = null, $attributes = array())
  {
    $image = $this->image($image, $alt, $attributes);
    $link = $this->to($url, $image);

    return $this->decode($link);
  }
}