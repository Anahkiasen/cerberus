<?php
namespace Cerberus\Controllers;

use Redirect;
use BaseController;
use View;
use Underscore\Types\Arrays;
use Underscore\Types\String;

class Base extends BaseController
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
      $core = preg_replace('/([a-z])\\\?([A-Z])/', '$1.$2', $class);

      // Compute controller
      $page = String::from($core)->remove('.Controller')->lower()->obtain();
      $this->controller = get_called_class();
      $this->page = $page;
    }

    // Define model
    $this->model  = String::from($core)->explode('.')->removeLast()->last()->singular()->title()->obtain();
    $this->item   = String::lower($this->model);
    $this->object = new $this->model();

    // Define fallback page
    $this->here = Redirect::action($this->controller.'@getIndex');

    // Share current page with view
    View::share('page', $this->page);
  }

  /**
   * Automatic routing
   */
  public function __call($method, $parameters)
  {
    // Get view name
    $view = preg_replace("/([a-z]+)([A-Z][a-z]+)/", '$2', $method);
    $view = $this->page.'.'.String::lower($view);

    // Return view if found
    try {
      $exists = View::getFinder()->find($view);
    }
    catch (InvalidArgumentException $e) {
      $exists = false;
    }
    if($exists) return View::make($view);

    // Else throw a 404
    return parent::__call($method, $parameters);
  }
}
