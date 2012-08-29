<?php

// Setup and starting up ------------------------------------------- /

// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'libraries'
));

use Cerberus\Toolkit\Buffer,
    Cerberus\Core\Dispatch;

// Custom Cerberus macros ------------------------------------------ /

HTML::macro('favicon', function($favicon)
{
  return "<link href='" .URL::to_asset($favicon). "' rel='shortcut icon' />";
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