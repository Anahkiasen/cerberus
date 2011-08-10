<?php
// Chargement du moteur Cerberus
include_once('cerberus/init.php');
$cerberus = new Cerberus(array('browserSelector', 'cssFont', '[sql]', 'createIndex', 'Desired'));
$productionMode = $cerberus->debugMode();
$index = createIndex();

// Arbre de navigation et page en cours
$navigation = array();
$desiredPage = new desiredPage($navigation);
list($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav, $pageVoulueFile) = $desiredPage->render();

// Dispatch des fonctions et API
$cerberus->cerberusDispatch(array());

list($css, $js, $thisScripts) = $cerberus->cerberusAPI(array());

// Connexion SQL
if(function_exists('connectSQL'))
	connectSQL('localbdd');

$thisAgent = browserSelector();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="<?= $thisAgent ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>[WEBSITE]</title>
<? cssFont(array('Open Sans')) ?>
<?= $css ?>
<link href="css/cerberus.css" rel="stylesheet" type="text/css" />
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body class="<?= $pageVoulue ?>">
	<div id="main">
		<div id="header"></div>
		<div id="menu"><?= $renderNav ?></div>
		<div id="corps"><? include_once('pages/' .$pageVoulueFile) ?></div>
		<div id="footer">&copy;Copyright <?= date('Y') ?> - [WEBSITE] - Concept : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
	
	<?= $js ?>
</body>
</html>