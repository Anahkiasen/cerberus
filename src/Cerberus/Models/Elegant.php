<?php
namespace Cerberus\Models;

use \Eloquent;
use \Input;
use \Validator;

abstract class Elegant extends Eloquent
{
  /**
   * The model's rules
   * @var array
   */
  public static $rules = array();

  // Helper methods ------------------------------------------------ /

  /**
   * Validates input against the model's rules
   *
   * @param  array $input The user input
   * @return Validator
   */
  public static function validate($input = null)
  {
    // If no input given, fetch all
    if (!$input) $input = Input::get();
    return Validator::make($input, static::$rules);
  }

  // Attributes ---------------------------------------------------- /

  /**
   * Prints out the model
   *
   * @return string [description]
   */
  public function __toString()
  {
    return (string) $this->name;
  }
}
