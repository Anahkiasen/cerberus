<?php

class CerberusBase extends Base_Controller
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
   * Restful controllers
   * @var boolean
   */
  public $restful = true;

  /**
   * Precreate object, page and model
   */
  public function __construct()
  {
    // Define page
    if (!$this->page) {
      $page = explode('_', get_called_class());
      $this->page = strtolower($page[0]);
    }

    // Define model
    $this->model  = ucfirst(rtrim($this->page, 's'));
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
   * Automatic routing
   */
  public function __call($method, $parameters)
  {
    // Get view name
    $view = array_get(explode('_', $method), 1, $method);
    $view = $this->page.'.'.$view;

    // Return view if found
    if(View::exists($view)) return View::make($view);

    // Else throw a 404
    return parent::__call($method, $parameters);
  }
}
