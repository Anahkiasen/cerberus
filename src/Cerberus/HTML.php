<?php
namespace Cerberus;

use App;
use Illuminate\Html\HtmlBuilder;
use Underscore\Methods\ArraysMethods as Arrays;
use Underscore\Methods\StringMethods as String;

/**
 * Various HTML helpers for common tasks
 */
class HTML extends HtmlBuilder
{

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// LINKS ///////////////////////////////
  ////////////////////////////////////////////////////////////////////

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
    $link = $this->link($url, $image);

    return $this->decode($link);
  }

  /**
   * Generates a link that opens in a new tab
   *
   * @param string $url        The link
   * @param string $link       Its text
   * @param array  $attributes Its attributes
   *
   * @return string A link with target=_blank
   */
  public function linkBlank($url, $link = null, $attributes = array())
  {
    $attributes['target'] = '_blank';

    return $this->link($url, $link, $attributes);
  }

  /**
   * Generates a link to the app's homepage
   *
   * @param string $text       The link text
   * @param array  $attributes Its attributes
   *
   * @return string A link that points to /
   */
  public function linkHome($text, $attributes = array())
  {
    return $this->link(null, $text, $attributes);
  }

  ////////////////////////////////////////////////////////////////////
  //////////////////////////////// TABLE /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * A link to a resource action
   *
   * @param string $method     The HTTP verb
   * @param string $route      The resource action
   * @param array  $parameters Addition parameters
   * @param array  $attributes Link attributes
   *
   * @return string An <a> tag
   */
  public function resource($method, $route, $text, $parameters = array(), $attributes = array())
  {
    // If we didn't directly pass an array of parameters
    if (!is_array($parameters)) {
      $parameters = array($parameters->getKey());
    }

    // Add verb to attributes
    $attributes['data-method'] = $method;

    $link = $this->route($route, $text, $parameters, $attributes);
    $link = $this->decode($link);

    return $link;
  }

  /**
   * Generates an "action" column
   *
   * @param string $link       A link, action or route name
   * @param string $icon       An icon to use
   * @param array  $parameters Link parameters
   *
   * @return string A <td> containing a link
   */
  public function actionColumn($link, $icon, $parameters)
  {
    // If we didn't directly pass an array of parameters
    if (!is_array($parameters)) {
      $parameters = array($parameters->getKey());
    }

    // Remember link as class
    $class = $link;
    $route = App::make('router')->getRoutes()->get('projects.track');

    // If the link is to a controller
    if (String::contains($link, '@')) {
      $class = String::explode($link, '@');
      $class = $class[1];
      $method = 'action';
    } elseif ($route) $method = 'route';
    else $method = 'to';

    // Parse and decode link
    $link = $this->url->$method($link, $parameters);
    $link = $this->link($link, "<i class='icon-$icon' />");
    $link = $this->decode($link);

    return '<td class="action ' .$class. '">'.$link.'</td>';
  }

  ////////////////////////////////////////////////////////////////////
  ////////////////////////////// HEAD TAGS ///////////////////////////
  ////////////////////////////////////////////////////////////////////

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

  ////////////////////////////////////////////////////////////////////
  //////////////////////////// MAGIC METHODS /////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Some shortcuts
   */
  public function __call($method, $parameters)
  {
    // Resource verbs
    if (String::endsWith($method, 'Resource')) {
      $verb       = String::remove($method, 'Resource');
      $parameters = Arrays::prepend($parameters, $verb);

      return call_user_func_array(array($this, 'resource'), $parameters);
    }
  }
}
