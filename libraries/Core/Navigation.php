<?php
namespace Cerberus\Core;

use Cerberus\Toolkit\Arrays;
use Cerberus\Toolkit\String;

class Navigation
{
  public static $default = 'home';

  /**
   * Returns the current controller/route
   *
   * @param  boolean $returnAction Whether the script should return the action too
   * @return mixed                 An array or a string
   */
  public static function current($returnAction = false)
  {
    // Get Request object
    $request = \Request::route();

    // If we have a Request object ready
    if (is_object($request)) {

      // If in a controller
      if ($request->controller) {
        $controller = $request->controller;
        $action     = $request->controller_action;

      // Else if in a route
      } else {
        $action = null;
        $route = $request->uri;
        $route = explode('/', $route);
        $controller = Arrays::get($route, 1, Arrays::get($route, 0));
        if(empty($controller)) $controller = static::$default;
      }

    // If we don't have object, try and parse the URL
    } else {
      $url = \URL::current();
      $url = String::remove(\URL::base(), $url);
      $url = explode('/', $url);

      // Remove hypothetical forward slash
      if (empty($url[0])) {
        array_shift($url);
      }

      $controller = Arrays::get($url, 0);
      $action = Arrays::get($url, 1);
    }

    return $returnAction ? array($controller, $action) : $controller;
  }

  /**
   * Gets classes for the current page as controller controller-action
   *
   * @return string CSS classes
   */
  public static function classes()
  {
    list($controller, $action) = self::current(true);

    return $action ? $controller. ' ' .$controller. '-' .$action : $controller;
  }

  /**
   * Checks if a page is the current one
   *
   * @param  string  $route A given route
   * @return boolean        True or false
   */
  public static function isCurrent($route = null)
  {
    return $route == self::current();
  }
}
