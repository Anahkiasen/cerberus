<?php
namespace Cerberus;

class ArrayObject extends \ArrayObject
{
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
    array_unshift($parameters, $this->getArrayCopy());

    // Rebind array to object
    $call = call_user_func_array('\Cerberus\Arrays::'.$method, $parameters);
    if (is_array($call)) $this->exchangeArray($call);
    else return $call;

    return $this;
  }
}