<?php
// Chargement du moteur Cerberus
require_once('cerberus/init.php');

// Arbre de navigation et page en cours
$desired->render();
$navigation = $desired->get();

// Dispatch des fonctions et API
$dispatch->getPHP(array());

$dispatch->getAPI(array());
?>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $cerberus->meta('titre') ?></title>
<meta name="description" content="<?= $cerberus->meta('description'); ?>" />
<? cssFont(array('Open Sans')) ?>
<? $dispatch->getCSS() ?>
</head>

<body class="<?= $desired->css() ?>">
	<div id="main">
		<div id="header"></div>
		<div id="menu"><?= $desired->getmenu() ?></div>
		<div id="corps"><? $desired->content() ?></div>
		<div id="footer"><?= $desired->footer() ?></div>
	</div>
	
	<? $dispatch->getJS() ?>
</body>
</html>
<? content::cache_end() ?>