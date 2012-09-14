<?php

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
  'CerberusController' => Bundle::path('cerberus') . 'controllers' . DS . 'base.php',
));

use Cerberus\Toolkit\Buffer,
    Cerberus\Toolkit\Language,
    Cerberus\Core\Dispatch,
    Cerberus\Modules\Backup;

// Set correct language
$locale = Language::locale();

/*
|---------------------------------------------------------------------
| Custom macros and validators
|---------------------------------------------------------------------
 */

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

// Datalist
HTML::macro('datalist', function($name, $list)
{
  $datalist = '<datalist id="' .$name. '">';
    foreach($list as $key => $value) {
      $datalist .= '<option value="' .$value. '">' .$key. '</option>';
    }
  $datalist .= '</datalist>';

  return $datalist;
});

// Table action
HTML::macro('action', function($action, $icon, $item)
{
  list($controller, $action) = explode('@', $action);

  return
    '<td class="action ' .$action. '">'.
      HTML::decode(HTML::link_to_action($controller.'@'.$action, Icons::$icon(), array($item->id))).
    '</td>';
});

// Full table row
HTML::macro('fullRow', function($content, $attributes = array())
{
  return
    '<tr' .HTML::attributes($attributes). '>
      <td colspan="50">' .$content. '</td>
    </tr>';
});

// Validate length
Validator::register('length', function($attribute, $value, $parameters)
{
  $length = Str::length(trim($value));
  return $length == $parameters[0];
});

// Check if a field contains text only (spaces, alpha etc)
Validator::register('not_numeric', function($attribute, $value)
{
  return preg_match('/^([^0-9]+)+$/i', $value);
});

/*
|---------------------------------------------------------------------
| Database backup
|---------------------------------------------------------------------
 */

$backup = new Backup;

// Save database
$backup->save();

// Remove old saves
$backup->cleanup();
