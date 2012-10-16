<?php

class CerberusRestful extends CerberusBase
{

  /**
   * Where the main for for this model will be
   * @var string
   */
  private $form = null;

  public function __construct()
  {
    parent::__construct();

    // Define form page
    $this->form = $this->page.'.create';
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

  public function get_index()
  {
    return View::make($this->page.'.index');
  }

  /**
   * Create an item
   */
  public function get_create()
  {
    return View::make($this->form)
      ->with_item(new $this->model())
      ->with_mode('create');
  }

  /**
   * Edit an item
   *
   * @param integer $item_id An item id
   */
  public function get_update($item_id)
  {
    // If we gave an ID, fetch corresponding object
    if (!is_object($item_id)) {

      // Fetch data from item
      $item = $this->object->find($item_id);

      // If invalid item, redirect to create form
      if(!$item) return Redirect::to_action($this->here.'@create');
    } else $item = $item_id;

    Former::populate($item);

    return View::make($this->form)
      ->with_item($item)
      ->with_mode('update');
  }

  /**
   * Custom post action to build upon
   */
  public function custom_update()
  {
    // Fetch input and its rules
    $input = Input::get();
    $isAdd = !array_get($input, 'id');
    $item  = $isAdd ? new $this->model() : $this->object->find($input['id']);

    // Autocomplete uniqueness rules
    $rules = $this->rules();
    foreach($rules as $field => $rulz) {
      if(str_contains($rulz, 'unique:')) {
        $modifiedRules = preg_replace('#unique:([^,]+)([^|,])(\||$)#', 'unique:$1$2,'.$field, $rulz);
        $modifiedRules = preg_replace('#unique:([^,]+)(,[^,]+)(\||$)#', 'unique:$1$2,'.$input['id'], $modifiedRules);
        $rules[$field] = $modifiedRules;
      }
    }

    // Get localized fields
    $model = $this->model;
    if(isset($model::$polyglot)) {
      $localization = Input::only($model::$polyglot);
      $input = Input::except($model::$polyglot);
    }

    // Validate input
    $validation = Validator::make($input, $rules);
    if ($validation->fails()) {
      $return = Redirect::to_action($this->page.'@'.($isAdd ? 'create' : 'update'), array($item->id))
        ->with_input()
        ->with('items', $item->id)
        ->with_errors($validation);

      return array(
        'new'    => $isAdd,
        'errors' => $validation,
        'model'  => null,
        'return' => $return,
        'state'  => false,
      );
    }

    // Save attributes
    $model = $this->model;
    if(!$isAdd) {
      $model::update($input['id'], $input);
      $model = $model::find($input['id']);
    }
    else $model = $model::create($input);

    // Update localized fields
    if(isset($localization)) {
      $model->localize($localization);
    }

    // Create message
    $verb = $isAdd ? 'create' : 'update';
    $message = Babel::restful(Str::singular($this->page), array_get($input, 'name'), $verb);

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
  public function post_update()
  {
    extract($this->custom_update());

    return $return->with('message', $message);
  }

  /**
   * Delete an item
   *
   * @param integer $item_id An item id
   */
  public function get_delete($item_id)
  {
    $item = $this->object->find($item_id);
    if(!$item) $state = false;

    // Get item name
    $name = $item ? $item->name : null;

    // Remove item
    if($item) {
      $state = $item->delete();
      $state = $state == 1;
    }

    // Create message
    $message = Babel::restful(Str::singular($this->page), $name, 'delete', $state);

    return $this->here
      ->with('message', $message);
  }
}
