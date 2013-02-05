<?php
/**
 *
 * Session
 * Handles all session fiddling
 *
 * @package Kirby
 */
namespace Cerberus\Toolkit;

use Cerberus\Toolkit\Arrays as a;

class Session
{
  //////////////////////////////////////////////////////////////////
  //////////////////////// MANAGING SESSION ////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
     * Starts a new session
     */
  public static function start()
  {
    @session_start();
  }

  /**
     * Starts a new session
     */
  public static function destroy()
  {
    @session_destroy();
  }

  /**
     * Destroys a session first and then starts it again
     */
  public static function restart()
  {
    self::destroy();
    self::start();
  }

  /**
   * Tries to guess whether a session is started or not
   * @return boolean Session state
   */
  public static function exists()
  {
    $id = self::id();

    return !empty($id);
  }

  //////////////////////////////////////////////////////////////////
  ////////////////////////// SESSION KEYS //////////////////////////
  //////////////////////////////////////////////////////////////////

  /**
     * Returns the current session id
     *
     * @return string
     */
  public static function id()
  {
    return @session_id();
  }

  /**
     * Sets a session value by key
     *
     * @param mixed $key   The key to define
     * @param mixed $value The value for the passed key
     */
  public static function set($key, $value = false)
  {
    if(!isset($_SESSION)) return false;

    // If we gave a set of keys to set, merge it with the session array
    if(is_array($key)) $_SESSION = array_merge($_SESSION, $key);

    // Else just add it in
    else $_SESSION[$key] = $value;
  }

  /**
     * Gets a session value by key
     *
     * @param mixed $key     The key to look for. Pass false or null to return the entire session array.
     * @param mixed $default Optional default value, which should be returned if no element has been found
     *
     * @return mixed
     */
  public static function get($key = false, $default = null)
  {
    if(!isset($_SESSION)) return false;

    // If no key was given, return the whole array
    if(empty($key)) return $_SESSION;

    // Else return wanted key
    return a::get($_SESSION, $key, $default);
  }

  /**
     * Removes a value from the session by key
     *
     * @param mixed $key The key to remove by
     *
     * @return array The session array without the value
     */
  public static function remove($key)
  {
    if(!isset($_SESSION)) return false;

    // Remove key from array
    $_SESSION = a::remove($_SESSION, $key);

    return $_SESSION;
  }
}
