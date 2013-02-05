<?php
/**
 * close.php
 * Adds some Cerberus magic into the page, cache it, and display it
 */

// Load classes
use Cerberus\Core\Dispatch,
    Cerberus\Core\Head,
    Cerberus\Core\Navigation,
    Cerberus\Toolkit\Cache,
    Cerberus\Toolkit\Content;

// Get body content
$modifiedBody = content::get();

// Add Javascript to the body tag
if ($init->loaded('dispatch')) {
  $modifiedBody = str_replace('</body>', dispatch::getJS(true).'</body>', $modifiedBody);
  dispatch::getCSS();
}

// Append current page to the body classes
$modifiedBody = preg_replace('#<body( class="(.+)")?>#', '<body class="' .navigation::css(). ' $2">', $modifiedBody);

// Add the head tag
$modifiedBody = str_replace('<head>', null, $modifiedBody);
$modifiedBody = head::header().$modifiedBody;

// Save the page and display it
echo $modifiedBody;
cache::save(); // Page en cache
