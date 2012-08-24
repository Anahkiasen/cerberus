<?php
/**
 *
 * Former
 *
 * Helper class to build Bootstrap forms and
 * populating them with models and validation
 */

namespace Cerberus\Modules;

use Cerberus\Toolkit\Arrays,
    Cerberus\Toolkit\String,
    Cerberus\Toolkit\Laravel,
    Laravel\Input;

// Start Bootstrapper to extend Form
\Bundle::start('bootstrapper');

class Former extends \Bootstrapper\Form
{
  /**
   * The form errors
   * @var array
   */
  private static $errors = array();

  /**
   * Additional values to be mapped to the form
   * @var array
   */
  private static $values = array();

  /**
   * Whether a fieldset is opened or not
   * @var boolean
   */
  private static $fieldnameset = false;

  /**
   * The rules of the current form
   * @var array
   */
  private static $rules = array();

  /**
   * Dynamically creates a validated/repopulated control-group
   *
   * @param  string $method     The method
   * @param  array  $parameters Parameters
   * @return string             A form field
   */
  public static function __callStatic($method, $parameters)
  {
    if(starts_with($method, 'add'))
      return self::createField($method, $parameters);

    $in = static::magic_input($method, $parameters);
    if($in !== null) return $in;

   return parent::__callStatic($method, $parameters);
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// PUBLIC API /////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Opens a fieldset with a legend
   * Closes any already opened fieldset before opening a new one
   *
   * @param  string $fieldname The fieldset's legend
   * @return string       Fieldset opening tag
   */
  public static function fieldset($fieldname)
  {
    if(self::$fieldnameset) self::closeFieldset();

    self::$fieldnameset = true;

    $fieldname = Laravel::translate($fieldname);
    $fieldname = ucfirst($fieldname);

    return '<fieldset>'.PHP_EOL.'<legend>'. $fieldname. '</legend>';
  }

  /**
   * Closes a fieldset
   *
   * @return string Fieldset closing tag
   */
  public static function closeFieldset()
  {
    return '</fieldset>';
  }

  /**
   * Set values for the form
   *
   * @param array $values An array of values
   */
  public static function setValues($values)
  {
    self::$values = $values;
  }

  /**
   * Set the Errors object
   *
   * @param object $errors A laravel Validator object
   */
  public static function setErrors($errors)
  {
    self::$errors = $errors;
  }

  /**
   * Pass a main object to the form for it to fetch rules
   * @param array $rules An array of rules
   */
  public static function setRules($rulesArray)
  {
    // Parse the rules strings into arrays
    foreach($rulesArray as $field => $rules) {
      $rulesArray[$field] = array();
      $rules = explode('|', $rules);
      foreach($rules as $rule) {
        list($rule, $parameters) = self::parse($rule);
        $rulesArray[$field][$rule] = $parameters;
      }
    }

    // Loop through rules and gather the one we can render live
    foreach($rulesArray as $field => $rules) {
      foreach($rules as $rule => $parameters) {
        switch($rule) {
          case 'required':
            self::$rules[$field]['required'] = '';
            break;
          case 'max':
            self::$rules[$field]['maxlength'] = array_get($parameters, 0);
            break;
          case 'no_numeric':
            self::$rules[$field]['pattern'] = '[^0-9]+';
            break;
        }
      }
    }
  }

  ////////////////////////////////////////////////////////////////////
  /////////////////////////// CORE FUNCTION //////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Creates a form field
   *
   * @param  string $method     The method
   * @param  array  $parameters Parameters
   * @return string             A form field
   */
  private static function createField($method, $parameters = array())
  {
    $type   = strtolower(substr($method, 3));
    list($label, $fieldname) =
      self::getLabelName(Arrays::get($parameters, 1), Arrays::get($parameters, 0));

    // Get field state
    $errors = self::getErrors($fieldname);
    $state  = self::getState($fieldname);

    // Get field parameters
    $offset     = $type == 'select' ? 3 : 2;
    $value      = self::getValue($fieldname, Arrays::get($parameters, $offset));
    $attributes = Arrays::get($parameters, $offset + 1, array());
    $help       = Arrays::get($parameters, $offset + 2);

    // Get prepend/append
    $prepend    = Arrays::get($attributes, 'prepend');
    $append     = Arrays::get($attributes, 'append');
    $attributes = Arrays::remove($attributes, array('prepend', 'append'));

    // Type check security
    if(!is_array($attributes))
      $attributes = array($attributes);

    // Adding rules to the attributes array
    $attributes = array_merge($attributes, array_get(self::$rules, $fieldname));

    // Creating the input
    switch ($type) {
      case 'password':
        $input = call_user_func('Form::'.$type, $fieldname, $attributes);
        break;

      case 'select';
        $select = Arrays::get($parameters, 2, array());
        $input = call_user_func('Form::'.$type, $fieldname, $select, $value, $attributes);
        break;

      default:
        $input = call_user_func('Form::'.$type, $fieldname, $value, $attributes);
        break;
    }

    // Append/prepend content
    if($prepend and $append)      $input = self::prepend_append($input, $prepend, $append);
    elseif($prepend and !$append) $input = self::prepend($input, $prepend);
    elseif(!$prepend and $append) $input = self::append($input, $append);

    // Replace inline help by errors
    if ($errors) {
      if(!String::find('help-inline', $help))
        $help = self::inline_help('').$help;

      $help = preg_replace(
        '#<span  ?class=" ?help-inline"([^>]*)>(.*)</span>#',
        '<span class="help-inline"$1>' .$errors. '</span>',
        $help);
    }

    // Return form
    return \Form::control_group(
      \Form::label($fieldname, $label),
      $input,
      $state,
      $help
    );
  }

  /**
   * Extract the rule name and parameters from a rule.
   *
   * @param  string  $rule
   * @return array
   */
  protected static function parse($rule)
  {
    $parameters = array();

    // The format for specifying validation rules and parameters follows a
    // {rule}:{parameters} formatting convention. For instance, the rule
    // "max:3" specifies that the value may only be 3 characters long.
    if (($colon = strpos($rule, ':')) !== false)
    {
      $parameters = str_getcsv(substr($rule, $colon + 1));
    }

    return array(is_numeric($colon) ? substr($rule, 0, $colon) : $rule, $parameters);
  }

  ////////////////////////////////////////////////////////////////////
  ///////////////////////////// HELPERS //////////////////////////////
  ////////////////////////////////////////////////////////////////////

  /**
   * Get the errors for a field
   *
   * @param  string $fieldname A field name
   * @return string        An error message
   */
  private static function getErrors($fieldname)
  {
    if(self::$errors)

      return self::$errors->first($fieldname);
  }

  /**
   * Get the Bootstrap state of a field
   * @param  string $fieldname A field name
   * @return string        A Boostrap state
   */
  public static function getState($fieldname)
  {
    if(!self::$errors) return null;

    return self::$errors->has($fieldname)
      ? 'error'
      : ((Input::has($fieldname) or Input::had($fieldname))
        ? 'success'
        : null);
  }

  private static function getLabelName($label, $fieldname = null)
  {
    if(!$fieldname) $fieldname = $label;

    // Check for the two possibilities
    if($label and !$fieldname) $fieldname = \Str::slug($label);
    elseif(!$label and $fieldname) $label = $fieldname;

    // Attempt to translate the label
    $label = Laravel::translate($label);
    $label = ucfirst($label);

    return array($label, $fieldname);
  }

  /**
   * Creates a label from a fieldname
   *
   * @param  string $label     The label string
   * @param  string $fieldname The field name
   * @return string            An html label
   */
  public static function getLabel($label, $fieldname = null)
  {
    list($label, $fieldname) = self::getLabelName('store_code', 'store_code');

    return \Form::label($fieldname, $label);
  }

  /**
   * Get the value of a field
   *
   * @param  string $fieldname A field name
   * @return string            A fallback field value
   */
  public static function getValue($fieldname, $value = null)
  {
    if($value) return $value;

    $value = is_object(self::$values) ? self::$values->{$fieldname} : null;

    return Input::get($fieldname, Input::old($fieldname, $value));
  }
}
