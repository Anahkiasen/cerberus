<?php
$page = content::end(true);

// Ressources
$page = str_replace('</body>', dispatch::getJS(true).'</body>', $page);

// Classe en fonction de la page
$page = str_replace('<body>', '<body class="">', $page);
$page = str_replace('<body class="', '<body class="' .navigation::css(). ' ', $page);

// Balises META
$page = str_replace('<head>', '<head>'.dispatch::getCSS(true), $page);
$page = str_replace('<head>', meta::head(), $page);

echo $page;
cache::save();
?>
