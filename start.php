<?php
use Cerberus\Modules\Backup;
use Cerberus\Toolkit\Language;

/*
|---------------------------------------------------------------------
| Setup and loadings
|---------------------------------------------------------------------
 */

// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'libraries'
));

// Map custom controller
Autoloader::map(array(
  'CerberusBase'    => Bundle::path('cerberus') . 'controllers' . DS . 'base.php',
  'CerberusRestful' => Bundle::path('cerberus') . 'controllers' . DS . 'restful.php',
  'Elegant'         => Bundle::path('cerberus') . 'models'      . DS . 'elegant.php',
));


// Set correct language
$locale = Language::locale();

/*
|---------------------------------------------------------------------
| Custom macros and validators
|---------------------------------------------------------------------
 */

include 'macros.php';

/*
|---------------------------------------------------------------------
| Helpers
|---------------------------------------------------------------------
 */

/**
 * Alias for HTML::decode
 *
 * @param  mixed  $content Content to unparse
 * @return string          Decoded content
 */
function d($content)
{
  return HTML::decode($content);
}

/*
|---------------------------------------------------------------------
| Database and language backup
|---------------------------------------------------------------------
 */

// Save language file every day ------------------------------------ /

Cache::remember('language', function() {
  return Language::compile('language.csv');
}, 60 * 24);

// Save database every day ----------------------------------------- /

if (!Request::env() and !Request::cli()) {

  $backup = new Backup;

  // Save database
  $backup->save();

  // Remove old saves
  $backup->cleanup();
}
