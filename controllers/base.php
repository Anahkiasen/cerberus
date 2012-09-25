<?php

class CerberusController extends Base_Controller
{

  /**
   * The current page
   *
   * @var string
   */
  public $page;

  /**
   * The name of the Model
   *
   * @var string
   */
  public $model = null;

  /**
   * An instance of the model
   *
   * @var object
   */
  public $object = null;

  public function __construct()
  {
    // Define model
    $this->model  = ucfirst(substr($this->page, 0, -1));
    $this->object = new $this->model();

    // Define fallback page
    $this->here = Redirect::to_action($this->page.'@index');

    parent::__construct();
  }

  /**
   * Get the model's rules
   *
   * @return array An array of rules
   */
  public function rules()
  {
    $model = $this->model;

    return $model::$rules;
  }

  public function post_update()
  {
    // Fetch input and its rules
    $input = Input::get();
    $isAdd = !array_get($input, 'id');
    $item  = $isAdd ? new $this->model() : $this->object->find($input['id']);

    // Validate form
    $validation = Validator::make($input, $this->rules());
    if ($validation->fails()) {

      return Redirect::to_action($this->page.'@'.($isAdd ? 'create' : 'update'), array($item_id))
        ->with_input()
        ->with('items', $item->id)
        ->with_errors($validation);
    }

    // Save attributes
    foreach($input as $attribute => $value) {
      $item->{$attribute} = $value;
    }
    $item->save();

    // Create message
    $message = $isAdd ? 'messages.' .$this->page. '.create' : 'messages.' .$this->page. '.update';
    if(isset($input['name'])) $message = Lang::line($message, array('name' => $input['name']));

    return $this->here;
  }

  /**
   * Delete an item
   *
   * @param  integer $item_id An item id
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
