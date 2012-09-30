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

  /**
   * Where the main for for this model will be
   * @var string
   */
  private $form = null;

  public function __construct()
  {
    // Define page
    if(!$this->page) {
      $page = explode('_', get_called_class());
      $this->page = strtolower($page[0]);
    }

    // Define model
    $this->model  = ucfirst(substr($this->page, 0, -1));
    $this->object = new $this->model();

    // Define fallback page
    $this->here = Redirect::to_action($this->page.'@index');

    // Define form page
    $this->form = $this->page.'.create';

    // Share current page with view
    View::share('page', $this->page);

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

    return isset($model::$rules) ? $model::$rules : array();
  }

  /**
   * Read all items
   */
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
    if(!is_object($item_id)) {

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
