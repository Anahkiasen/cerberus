<?php
/**
 * close.php
 * Adds some Cerberus magic into the page, cache it, and display it
 */

$body = content::end(true);

// Add Javascript to the body tag
$modifiedBody = str_replace('</body>', dispatch::getJS(true).'</body>', $body);

// Append current page to the body classes
$modifiedBody = preg_replace('#<body( class="(.+)")?>#', '<body class="' .navigation::css(). ' $2">', $modifiedBody);

// Add the head tag
dispatch::getCSS();
$modifiedBody = str_replace('<head>', null, $modifiedBody);
$modifiedBody = head::header().$modifiedBody;

// Save the page and display it
echo $modifiedBody; $cached = cache::save(true); // Page en cache
echo $cached; $cached = content::end(true); // Page réelle
echo $cached;
