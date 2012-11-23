<?php
/**
 *
 * HTML
 *
 * Various HTML helpers
 */
namespace Cerberus;

use \Babel\Babel;
use \Bootstrapper\Button;
use \Bootstrapper\Icon;
use \Router;
use \URL;

class HTML extends \Laravel\HTML
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
  public static function image_link($url, $image, $alt = null, $attributes = array())
  {
    $image = HTML::image($image, $alt, $attributes);

    return HTML::decode(HTML::link($url, $image));
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
  public static function blank_link($url, $link = null, $attributes = array())
  {
    $attributes['target'] = '_blank';

    return HTML::link($url, $link, $attributes);
  }

  /**
   * Generates a link to the app's home
   *
   * @param string $text       The link's text
   * @param array  $attributes Its attributes
   *
   * @return string A link using URL::home
   */
  public static function link_to_home($text, $attributes = array())
  {
    return HTML::link(URL::home(), $text, $attributes);
  }

  /**
   * Generates a favicon
   *
   * @param string $favicon Path to the favicon
   *
   * @return string A shortcut icon link
   */
  public static function favicon($favicon)
  {
    return "<link href='" .URL::to_asset($favicon). "' rel='shortcut icon' />";
  }

  /**
   * Adds the base tags for responsive design
   *
   * @return string Meta tags
   */
  public static function responsive()
  {
    $meta  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
    $meta .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
    $meta .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

    return $meta;
  }

  /**
   * Generates a datalist
   *
   * @param string $name Datalist ID
   * @param array  $list Its content
   *
   * @return string A datalist
   */
  public static function datalist($name, $list)
  {
    $datalist = '<datalist id="' .$name. '">';
      foreach ($list as $key => $value) {
        $datalist .= '<option value="' .$value. '">' .$key. '</option>';
      }
    $datalist .= '</datalist>';

    return $datalist;
  }

  /**
   * Generates an "Add item" buttonClass
   *
   * @param string $link                 The link its pointing to
   * @param string $supplementaryClasses Classes to add to the link
   */
  public static function add_button($link, $supplementaryClasses = null)
  {
    $buttonClass = 'block_large_primary_'.$supplementaryClasses.'link';

    return Button::$buttonClass(action($link.'@create'), Babel::add($link));
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
  public static function action($link, $icon, $parameters)
  {
    // If we didn't directly pass an array of parameters
    if (!is_array($parameters)) {
      $parameters = array($parameters->id);
    }

    // If the link is to a controller
    if (str_contains($link, '@')) {
      $class = array_get(explode('@', $link), 1);
      $link  = action($link, $parameters);
    }

    // If the link is a route
    elseif (Router::find($link)) {
      $class = $link;
      $link  = route($link, $parameters);
    }

    // Else just point to it
    else {
      $class = $link;
      $link = url($link, $parameters);
    }

    return
      '<td class="action ' .$class. '">'.
        d(HTML::link($link, Icon::$icon())).
      '</td>';
  }
}
