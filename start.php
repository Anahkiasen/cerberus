<?php

// Setup and starting up ------------------------------------------- /

// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'libraries'
));

use Cerberus\Toolkit\Buffer,
    Cerberus\Toolkit\Language,
    Cerberus\Core\Dispatch;

// Cache and builds ------------------------------------------------ /

/*if(Request::method() == 'GET') {

  list($controller, $action) = Cerberus\Core\Navigation::current(true);
  $identifier = ($controller and $action) ? $controller.'-'.$action : null;

  // Create settings array
  $cacheSettings = Config::get('cerberus.cache', array());

  // If the page can be cached
  if($identifier and
    in_array($identifier, $cacheSettings) or
    in_array($controller, $cacheSettings)) {

    // Create cache md5
    $url = Request::method().URL::current();
    $urlSlug = String::slugify($url);
    $id = md5($urlSlug);

    // Load cache if found
    if(Cache::has($id)) {
      if (!headers_sent()) header('Content-Type: text/html; charset=utf-8');
      echo Cache::get($id);
      exit();
    }

    // Else write cache and display
    Event::listen('laravel.done', function($id) use ($id)
    {
      $content = Buffer::get();
      Cache::forever($id, $content);
      echo $content;
    });
  }
}*/

// Language and localization --------------------------------------- /

// Set correct language
$locale = Language::locale();

// Custom Cerberus macros ------------------------------------------ /

// Favicon
HTML::macro('favicon', function($favicon)
{
  return "<link href='" .URL::to_asset($favicon). "' rel='shortcut icon' />";
});

// Responsive design
HTML::macro('responsive', function()
{
  $meta  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
  $meta .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
  $meta .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

  return $meta;
});

// Validate length
Validator::register('length', function($attribute, $value, $parameters)
{
  $length = Str::length(trim($value));
  return $length == $parameters[0];
});
