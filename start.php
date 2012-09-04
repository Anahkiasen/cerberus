<?php

// Setup and starting up ------------------------------------------- /

// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'libraries'
));

use Cerberus\Toolkit\Buffer,
    Cerberus\Toolkit\Language,
    Cerberus\Core\Dispatch;

// Set correct language
$test = Language::locale();

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

// Post-processing ------------------------------------------------- /

// Start output buffer
Buffer::start();

// Rewrite file
Event::listen('laravel.NO', function()
{
  $content = Buffer::get();

  // Add styles
  $content = str_replace('</head>', Dispatch::styles().'</head>', $content);

  // Add scripts
  $content = str_replace('</body>', Dispatch::scripts().'</head>', $content);

  echo $content;
});