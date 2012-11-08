<?php

//////////////////////////////////////////////////////////////////////
/////////////////////////// HTML MACROS //////////////////////////////
//////////////////////////////////////////////////////////////////////

// Links helpers --------------------------------------------------- /

HTML::macro('image_link', function($url, $image, $alt = null, $attributes = array()) {
  $image = HTML::image($image, $alt, $attributes);

  return HTML::decode(HTML::link($url, $image));
});

/**
 * Creates an HTML link that opens in another tab
 */
HTML::macro('blank_link', function($url, $link = null, $attributes = array()) {
  $attributes['target'] = '_blank';

  return HTML::link($url, $link, $attributes);
});

/**
 * Creates a link back to the homepage
 */
HTML::macro('link_to_home', function($text, $attributes = array()) {
  return HTML::link(URL::home(), $text, $attributes);
});

// HEAD helpers ---------------------------------------------------- /

/**
 * Adds a favicon to the website
 */
HTML::macro('favicon', function($favicon) {
  return "<link href='" .URL::to_asset($favicon). "' rel='shortcut icon' />";
});

/**
 * Adds the base tags for responsive design
 */
HTML::macro('responsive', function() {
  $meta  = "<meta name='apple-mobile-web-app-capable' content='yes' />".PHP_EOL;
  $meta .= "<meta name='apple-touch-fullscreen' content='yes' />".PHP_EOL;
  $meta .= "<meta name='viewport' content='width=device-width, initial-scale=1.0' />".PHP_EOL;

  return $meta;
});

// Markup helpers -------------------------------------------------- /

/**
 * Generates a datalist
 */
HTML::macro('datalist', function($name, $list) {

  $datalist = '<datalist id="' .$name. '">';
    foreach ($list as $key => $value) {
      $datalist .= '<option value="' .$value. '">' .$key. '</option>';
    }
  $datalist .= '</datalist>';

  return $datalist;
});

/**
 * Generates an 'Add item' button
 */
HTML::macro('addButton', function($link, $supplementaryClasses = null) {
  $buttonClass = 'block_large_primary_'.$supplementaryClasses.'link';

  return Button::$buttonClass(action($link.'@create'), Babel::add($link));
});

/**
 * Adds an action column to a table
 */
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