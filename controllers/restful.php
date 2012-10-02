<?php

class CerberusRestful extends CerberusBase
{

  /**
   * Where the main for for this model will be
   * @var string
   */
  private $form = null;

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
   * Update an item's data
   */
  public function post_update()
  {
    // Fetch input and its rules
    $input = Input::get();
    $isAdd = !array_get($input, 'id');
    $item  = $isAdd ? new $this->model() : $this->object->find($input['id']);

    // Validate form
    $validation = Validator::make($input, $this->rules());
    if ($validation->fails()) {
      return Redirect::to_action($this->page.'@'.($isAdd ? 'create' : 'update'), array($item->id))
        ->with_input()
        ->with('items', $item->id)
        ->with_errors($validation);
    }

    // Save attributes
    $model = $this->model;
    if(!$isAdd) $model::update($input['id'], $input);
    else $model::create($input);

    // Create message
    $message = $isAdd ? 'messages.' .$this->page. '.create' : 'messages.' .$this->page. '.update';
    if(isset($input['name'])) $message = Lang::line($message, array('name' => $input['name']));

    return $this->here;
  }

  /**
   * Delete an item
   *
   * @param integer $item_id An item id
   */
  public function get_delete($item_id)
  {
    $item = $this->object->find($item_id);
    if(!$item) return $this->here;

    // Remove item
    $item->delete();

    // Create message
    $message = Lang::line(
      'messages.' .$this->page. '.delete',
      array('name' => $item->name));

    return $this->here
      ->with('message', $message);
  }
}
