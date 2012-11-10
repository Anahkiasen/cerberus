<?php
use Cerberus\Backup;
use Cerberus\Language;

/*
|---------------------------------------------------------------------
| Setup and loadings
|---------------------------------------------------------------------
 */

// Autoload Cerberus
Autoloader::namespaces(array(
  'Cerberus' => Bundle::path('cerberus') . 'src' .DS. 'Cerberus',
));

// Map custom controllers and models
Autoloader::alias('Cerberus\Controllers\Base', 'CerberusBase');
Autoloader::alias('Cerberus\Controllers\Restful', 'CerberusRestful');
Autoloader::alias('Cerberus\Models\Elegant', 'Elegant');
Autoloader::alias('Cerberus\Models\Polyglot', 'Polyglot');

// Set correct language
$locale = Language::locale();

/*
|---------------------------------------------------------------------
| Custom macros and validators
|---------------------------------------------------------------------
 */

// Validate length
Validator::register('length', function($attribute, $value, $parameters) {
  $length = Str::length(trim($value));

  return $length == $parameters[0];
});

// Check if a field contains text only (spaces, alpha etc)
Validator::register('not_numeric', function($attribute, $value) {
  return preg_match('/^([^0-9]+)+$/i', $value);
});

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
| Illuminate\Glow
|---------------------------------------------------------------------
 */

Route::get('glow.js', function() {

  Config::set('application.profiler', false);

  // Get Illuminate's glow
  $js = File::get('public/bundles/cerberus/js/glow.js');
  $js = str_replace('%BASE%', URL::base().'/', $js);

  // Set correct header
  $headers['Content-Type'] = 'application/javascript; charset=utf-8';

  return new Response($js, 200, $headers);
});

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
