<?php
// Chargement du moteur Cerberus
include_once('cerberus/init.php');
$cerberus = new Cerberus(array('browserSelector', 'cssFont', 'createIndex', 'pack.sql', 'pack.navigation'));
connectSQL([BDD]);

// Langues
$index = createIndex(array('fr'));

// Globales
$meta = $cerberus->meta();
$thisAgent = browserSelector();
$rewriteMode = false;

// Arbre de navigation et page en cours
$navigation = array(
	);
$desiredPage = new desiredPage($navigation);
list($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav, $pageVoulueFile) = $desiredPage->render();

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

<body class="<?= $pageVoulue ?>">
	<div id="main">
		<div id="header"></div>
		<div id="menu"><?= $renderNav ?></div>
		<div id="corps"><? if(file_exists('pages/' .$pageVoulueFile)) include_once('pages/' .$pageVoulueFile) ?></div>
		<div id="footer">&copy;Copyright <?= date('Y') ?> - [WEBSITE] - Concept : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
	
	<?= $js ?>
</body>
</html>