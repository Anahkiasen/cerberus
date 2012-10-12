<?php
/**
 *
 * Siri
 *
 * Transforms actions, objects and results
 * into readable sentences
 */
namespace Cerberus\Modules;

class Siri
{
  /**
   * Builds a restful message
   *
   * @param  string  $page   The current page
   * @param  string  $object The object's name
   * @param  string  $verb   The CRUD verb
   * @param  boolean $string The state of the action (failed or succeeded)
   * @param  string  $accord An accord to append the verb
   * @return string          A text message
   */
  public static function message($page, $object, $verb, $state = true, $accord = null)
  {
    // Recognize common accords
    if(in_array($page, array('categories'))) {
      $accord = 'e';
    }

    // Get main variables
    $noun  = __('cerberus::siri.nouns.'.$page). ' ';
    $name  = $object ? '&laquo; ' .$object. ' &raquo; ' : null;
    $bool  = $state ? 'success' : 'error';
    $state = __('cerberus::siri.state.'.$bool). ' ';
    $verb  = __('cerberus::siri.verbs.'.$verb);

    return \Alert::$bool($noun.$name.$state.$verb.$accord, false);
  }

  /**
   * Fetch and display a message from session
   *
   * @return string A message in an alert
   */
  public static function displayMessage()
  {
    if(\Session::has('message')) {
      return \Session::get('message');
    }
  }
}