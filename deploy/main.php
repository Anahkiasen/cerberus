<?php
// Chargement du moteur Cerberus
require_once('cerberus/init.php');

// Arbre de navigation et page en cours
$desired = new navigation();
$desiredPage->render($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav);
$navigation = $desired->get();

// Dispatch des fonctions PHP
$cerberus->cerberusDispatch(array(
	));

// Dispatch des fonctions JS
list($css, $js, $thisScripts) = $cerberus->cerberusAPI(array(
		));
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="<?= $thisAgent ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title><?= $cerberus->meta('titre') ?></title>
<meta name="description" content="<?= $cerberus->meta('description'); ?>" />
<? cssFont(array('Open Sans')) ?>
<?= $css ?>
</head>

<body class="<?= $desired->css() ?>">
	<div id="main">
		<div id="header"></div>
		<div id="menu"><?= $renderNav ?></div>
		<div id="corps"><? $desiredPage->content() ?></div>
		<div id="footer">&copy;Copyright <?= date('Y') ?> - [WEBSITE] - Concept : <?= str::link('http://www.stappler.fr/', 'Le Principe de Stappler') ?></div>
	</div>
	
	<?= $js ?>
</body>
</html>