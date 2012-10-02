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
));


// Set correct language
$locale = Language::locale();

/*
|---------------------------------------------------------------------
| Custom macros and validators
|---------------------------------------------------------------------
 */

// Favicon
HTML::macro('favicon', function($favicon) {
  return "<link href='" .URL::to_asset($favicon). "' rel='shortcut icon' />";
});

// Responsive design
HTML::macro('responsive', function() {
  $meta  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
  $meta .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
  $meta .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

  return $meta;
});

// Datalist
HTML::macro('datalist', function($name, $list) {

  $datalist = '<datalist id="' .$name. '">';
    foreach ($list as $key => $value) {
      $datalist .= '<option value="' .$value. '">' .$key. '</option>';
    }
  $datalist .= '</datalist>';

  return $datalist;
});

// Table action
HTML::macro('action', function($link, $icon, $item) {

  // If the link is to a controller
  if (str_contains($link, '@')) {
    $class = array_get(explode('@', $link), 1);
    $link  = action($link, array($item->id));
  }

  // If the link is a route
  elseif (Router::find($link)) {
    $class = $link;
    $link  = route($link, array($item->id));
  }

  // Else just point to it
  else {
    $class = $link;
    $link = url($link, array($item->id));
  }

  return
    '<td class="action ' .$class. '">'.
      HTML::decode(HTML::link($link, Icons::$icon())).
    '</td>';
});

// Table add button
HTML::macro('addButton', function($link, $text, $supplementaryClasses = null) {
  $buttonClass = 'block_large_primary_'.$supplementaryClasses.'link';

  return Tables::full_row(Buttons::$buttonClass(action($link), $text));
});

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
| Database backup
|---------------------------------------------------------------------
 */

// If not in local or testing or whatever
if (!Request::env()) {

  $backup = new Backup;

  // Save database
  $backup->save();

  // Remove old saves
  $backup->cleanup();
}
