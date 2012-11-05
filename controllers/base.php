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
      $class = get_called_class();

      // Compute controller
      $controller = str_replace('_', '.', $class);
      $controller = str_replace('.Controller', null, $controller);
      $this->controller = strtolower($controller);

      // Compute main page
      $page = explode('_', $class);
      $page = $page[sizeof($page) - 2];
      $this->page = strtolower($page);
    }

    // Define model
    $this->model  = ucfirst(Str::singular($this->page));
    $this->object = new $this->model();

    // Define fallback page
    $this->here = Redirect::to_action($this->controller.'@index');

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
    $view = $this->controller.'.'.$view;

    // Return view if found
    if(View::exists($view)) return View::make($view);

    // Else throw a 404
    return parent::__call($method, $parameters);
  }
}
