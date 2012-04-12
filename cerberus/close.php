<?php
$body = content::end(true);

// Modifications Cerberus
$modified_body = str_replace('</body>', dispatch::getJS(true).'</body>', $body);
$modified_body = preg_replace('#<body( class="(.+)")?>#', '<body class="' .navigation::css(). ' $2">', $modified_body);
$modified_body = str_replace('<head>', NULL, $modified_body);
$modified_body = meta::head().dispatch::getCSS(true).$modified_body;

echo $modified_body; $cached = cache::save(true); // Page en cache
echo $cached; content::end(); // Page réelle
?>