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

  /**
   * Generates a favicon
   *
   * @param string $favicon Path to the favicon
   *
   * @return string A shortcut icon link
   */
  public function favicon($favicon)
  {
    return "<link href='" .$this->url->asset($favicon). "' rel='shortcut icon' />";
  }

  /**
   * Adds the base tags for responsive design
   *
   * @return string A serie of meta tags
   */
  public function responsiveTags()
  {
    $meta  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
    $meta .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
    $meta .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

    return $meta;
  }
}