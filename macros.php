<?php

//////////////////////////////////////////////////////////////////////
/////////////////////////// HTML MACROS //////////////////////////////
//////////////////////////////////////////////////////////////////////


// Link in another tab/window
HTML::macro('blank_link', function($url, $link = null, $attributes = array()) {
  $attributes['target'] = '_blank';

  return HTML::link($url, $link, $attributes);
});

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

// Table add button
HTML::macro('addButton', function($link, $supplementaryClasses = null) {
  $buttonClass = 'block_large_primary_'.$supplementaryClasses.'link';

  return Button::$buttonClass(action($link.'@create'), Babel::add($link));
});

// Table action
HTML::macro('action', function($link, $icon, $parameters) {

  // If we didn't directly pass an array of parameters
  if(!is_array($parameters)) {
    $parameters = array($parameters->id);
  }

  // If the link is to a controller
  if (str_contains($link, '@')) {
    $class = array_get(explode('@', $link), 1);
    $link  = action($link, $parameters);
  }

  // If the link is a route
  elseif (Router::find($link)) {
    $class = $link;
    $link  = route($link, $parameters);
  }

  // Else just point to it
  else {
    $class = $link;
    $link = url($link, $parameters);
  }

  return
    '<td class="action ' .$class. '">'.
      d(HTML::link($link, Icon::$icon())).
    '</td>';
});

//////////////////////////////////////////////////////////////////////
///////////////////////// VALIDATOR MACROS ///////////////////////////
//////////////////////////////////////////////////////////////////////

// Validate length
Validator::register('length', function($attribute, $value, $parameters) {
  $length = Str::length(trim($value));

  return $length == $parameters[0];
});

// Check if a field contains text only (spaces, alpha etc)
Validator::register('not_numeric', function($attribute, $value) {
  return preg_match('/^([^0-9]+)+$/i', $value);
});