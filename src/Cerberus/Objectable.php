<?php
namespace Cerberus;

class Objectable
{
  /**
   * The working array
   * @var array
   */
  private $array = array();

  /**
   * Static alias for creation
   */
  public static function from($array)
  {
    return new static($array);
  }

  /**
   * Create a new ArrayObject
   *
   * @param array $array The array to create from
   */
  public function __construct($array)
  {
    $this->array = $array;

    return $this;
  }

  /**
   * Redirect missing chained calls to Arrays class
   *
   * @param string $method     The method called
   * @param array  $parameters Its parameters
   *
   * @return Arrays
   */
  public function __call($method, $parameters)
  {
    // Add Arrays instance to parameters
    array_unshift($parameters, $this->array);

    // Rebind array to object
    $call = call_user_func_array('\Cerberus\Arrays::'.$method, $parameters);
    if (is_array($call)) $this->array = $call;
    else return $call;

    return $this;
  }

  /**
   * Get a value
   *
   * @param string $key The value to get
   * @return mixed
   */
  public function _get($key)
  {
    return $this->array[$key];
  }

  /**
   * Set a value
   *
   * @param string $key   The key
   * @param mixed  $value Its value
   */
  public function __set($key, $value)
  {
    $this->array[$key] = $value;
  }

  /**
   * Return this object as an array
   *
   * @return array
   */
  public function obtain()
  {
    return $this->array;
  }

  // Setters ------------------------------------------------------- /

  /**
   * Swap an existing key with a new key/value
   *
   * @param string $replace The key to remove
   * @param string $with    The key to replace with
   * @param string $value   The value to replace with
   */
  public function swap($replace, $with, $value)
  {
    $this->$with = $value;
    $this->remove($replace);

    return $this;
  }

  // Getters ------------------------------------------------------- /

  /**
   * Get the first element of the array
   *
   * @return mixed
   */
  public function first()
  {
    $array = $this->array;

    return array_shift($array);
  }

  /**
   * Get the last element of the array
   *
   * @return mixed
   */
  public function last()
  {
    $array = $this->array;

    return array_pop($array);
  }

  public function count()
  {
    return sizeof($this->array);
  }
}