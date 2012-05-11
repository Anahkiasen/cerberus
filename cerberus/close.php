<?php
$body = content::end(true);

// Add Javascript to the body tag
$modified_body = str_replace('</body>', dispatch::getJS(true).'</body>', $body);

// Append current page to the body classes
$modified_body = preg_replace('#<body( class="(.+)")?>#', '<body class="' .navigation::css(). ' $2">', $modified_body);

// Add the head tag
dispatch::getCSS();
$modified_body = str_replace('<head>', NULL, $modified_body);
$modified_body = head::header().$modified_body;

// Save the page and display it
echo $modified_body; $cached = cache::save(true); // Page en cache
echo $cached; $cached = content::end(true); // Page réelle
echo $cached;
?>