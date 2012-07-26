<?php
// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'librairies'
));

use Cerberus\Toolkit\Buffer,
    Cerberus\Core\Dispatch;

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