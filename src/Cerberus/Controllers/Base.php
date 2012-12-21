<?php
namespace Cerberus\Controllers;

use \Redirect;
use \Base_Controller;
use \Str;
use \View;
use \Underscore\Types\Arrays;
use \Underscore\Types\String;

class Base extends Base_Controller
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
      $class = String::replace($class, '_', '.');

      // Compute controller
      $controller = String::from($class)->remove('.Controller')->lower();
      $this->controller = $controller->obtain();
      $this->page = $this->controller;
    }

    // Define model
    $this->model  = $controller->explode('.')->last()->singular()->title()->obtain();
    $this->item   = String::lower($this->model);
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
