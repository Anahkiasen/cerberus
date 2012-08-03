<?php
namespace Cerberus\Core;

class Navigation
{
  /**
   * Gets classes for the current page as controller controller-action
   *
   * @return string CSS classes
   */
  public static function classes()
  {
    $current = \Request::route();
    $current =
      $current->controller. ' ' .
      $current->controller.'/'.$current->controller_action;
    $current = str_replace('/', '-', $current);

    return $current;
  }

  /**
   * Checks if a page is the current one
   *
   * @param  string  $route A given route
   * @return boolean        True or false
   */
  public static function isCurrent($route = null)
  {
    return \URL::to($route) == \Request::route()->controller;
  }
}