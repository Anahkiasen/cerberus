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
