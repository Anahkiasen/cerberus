<?php
namespace Cerberus\Controllers;

use DB;
use Former\Facades\Illuminate as Former;
use Input;
use Redirect;
use Underscore\Methods\ArraysMethods as Arrays;
use Underscore\Methods\StringMethods as String;
use Validator;
use View;

class Restful extends Base
{

  /**
   * Where the main for for this model will be
   * @var string
   */
  protected $form = null;

  public function __construct()
  {
    parent::__construct();

    // Define form page if it isn't already
    if (!$this->form) {
      $this->form = $this->page.'.create';
    }
  }

  /**
   * Get the model's rules
   *
   * @return array An array of rules
   */
  public function rules()
  {
    $model = $this->model;

    return isset($model::$rules) ? $model::$rules : array();
  }

  /**
   * Display all items
   */
  public function getIndex()
  {
    $items = $this->object->all();
    $variable = String::plural($this->model);
    $variable = String::lower($variable);

    return View::make($this->page.'.index')
      ->with($variable, $items);
  }

  /**
   * Create an item
   */
  public function getCreate()
  {
    return View::make($this->form)
      ->with('item', new $this->model())
      ->with('mode', 'create');
  }

  /**
   * Edit an item
   *
   * @param integer $item_id An item id
   */
  public function getUpdate($item_id)
  {
    // If we gave an ID, fetch corresponding object
    if (!is_object($item_id)) {

      // Fetch data from item
      $item = $this->object->find($item_id);

      // If invalid item, redirect to create form
      if(!$item) return Redirect::action($this->here.'@getCreate');
    } else $item = $item_id;

    // Populate form if Former is installed
    if (class_exists('Former')) {
      Former::populate($item);
    }

    return View::make($this->form)
      ->with('item', $item)
      ->with('mode', 'update');
  }

  /**
   * Custom post action to build upon
   */
  public function customUpdate()
  {
    // Filter out foreign input that aren't model-related
    $attributes = (array) DB::table($this->object->getTable())->first();
    $attributes = array_keys($attributes);

    // If no model already exists, attempt a SHOW COLUMNS
    if (!$attributes) {
      $attributes = array_pluck(DB::query('SHOW COLUMNS FROM ' .$this->object->getTable()), 'field');
    }

    // Fetch input and its rules
    $input   = Input::all();
    $item_id = Arrays::get($input, 'id');
    $isAdd   = !$item_id;
    $item    = $isAdd ? new $this->model() : $this->object->find($item_id);

    // Autocomplete uniqueness rules
    $rules = $this->rules();
    foreach ($rules as $field => $rulz) {
      if (str_contains($rulz, 'unique:')) {
        $modifiedRules = preg_replace('#unique:([^,]+)([^|,])(\||$)#', 'unique:$1$2,'.$field, $rulz);
        $modifiedRules = preg_replace('#unique:([^,]+)(,[^,]+)(\||$)#', 'unique:$1$2,'.$item_id, $modifiedRules);
        $rules[$field] = $modifiedRules;
      }
    }

    // Get localized fields
    $model = $this->model;
    if (isset($model::$polyglot)) {
      $localization = Input::only($model::$polyglot);
    }

    // Validate input
    if ($rules) {
      $validation = Validator::make($input, $rules);
      if ($validation->fails()) {
        $return = Redirect::action($this->controller.'@'.($isAdd ? 'getCreate' : 'getUpdate'), array($item->id))
          ->withInput()
          ->with('items', $item->id)
          ->withErrors($validation);

        return array(
          'new'     => $isAdd,
          'errors'  => $validation,
          'message' => null,
          'model'   => null,
          'return'  => $return,
          'state'   => false,
        );
      }
    }

    // Save attributes
    $model = $item->fill($input);
    $model->touch();

    // Update localized fields
    if (isset($localization)) {
      $model->localize($localization);
    }

    // Create message
    $verb = $isAdd ? 'create' : 'update';
    //$message = Babel::restful($this->item, Arrays::get($input, 'name'), $verb);
    $message = 'The item ' .$input['name']. ' was correctly updated';

    return array(
      'errors'  => false,
      'message' => $message,
      'model'   => $model,
      'new'     => $isAdd,
      'return'  => $this->here,
      'state'   => true,
    );
  }

  /**
   * Update an item's data
   */
  public function postUpdate()
  {
    extract($this->customUpdate());

    return $return->with('message', $message);
  }

  /**
   * Delete an item
   */
  public function customDelete($item)
  {
    if(!is_object($item)) $item = $this->object->find($item);
    if(!$item) $state = false;

    // Get item name
    $name = $item ? $item->name : null;

    // Remove item
    if ($item) {
      $state = $item->delete();
      $state = $state == 1;
    }

    // Create message
    //$message = Babel::restful($this->item, $name, 'delete', $state);
    $message = 'The item '.$name. ' was correctly deleted';

    return array(
      'message' => $message,
      'model'   => $item,
      'return'  => $this->here->with('message', $message),
      'state'   => $state,
    );
  }

  /**
   * Delete an item
   *
   * @param integer $item_id An item id
   */
  public function getDelete($item_id)
  {
    extract($this->customDelete($item_id));

    return $return;
  }
}
