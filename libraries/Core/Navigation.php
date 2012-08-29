<?php
namespace Cerberus\Core;

use Cerberus\Toolkit\Arrays;

class Navigation
{
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

    // Get controller, action and route
    if ($request->controller) {
      $controller = $request->controller;
      $action     = $request->controller_action;
    } else {
      $action = null;
      $route = \Request::route()->uri;
      $route = explode('/', $route);
      $controller = Arrays::get($route, 1);
      if(empty($controller)) $controller = 'home';
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

    return $action ? $controller. '-' .$action : $controller;
  }

  /**
   * Checks if a page is the current one
   *
   * @param  string  $route A given route
   * @return boolean        True or false
   */
  public static function isCurrent($route = null)
  {
    return \URL::to($route) == self::current();
  }
}