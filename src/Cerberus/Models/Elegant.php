<?php
namespace Cerberus\Models;

use \Eloquent;

abstract class Elegant extends Eloquent
{
  /**
   * The model's rules
   * @var array
   */
  public static $rules = array();

  // Attributes ---------------------------------------------------- /

  public function __toString()
  {
    return (string) $this->name;
  }
}
