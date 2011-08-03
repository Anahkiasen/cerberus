<?php
// Chargement du moteur Cerberus
include_once('cerberus/init.php');
$cerberus = new Cerberus(array('browserSelector', 'cssFont', '[sql]', 'createIndex', 'Desired'));
$rewriteMode = ($_SERVER['HTTP_HOST'] == 'localhost:8888') ? false : false;
$productionMode = true;
$index = createIndex();

// Connexon à la base
if(function_exists('connectSQL')) 
	connectSQL([WEBDB]);

// Arbre de navigation et page en cours
$navigation = array();
$desiredPage = new desired($navigation, $rewriteMode, true);
list($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav, $pageVoulueFile) = $desiredPage->desired;

// Dispatch des fonctions et API
$cerberus->cerberusDispatch(
	// PAGES
	), $pageVoulue.$sousPageVoulue);

list($css, $js, $thisScripts) = $cerberus->cerberusAPI(array(
	// PAGES
	), $pageVoulue.$sousPageVoulue);
	
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
		<div id="footer">&copy;Copyright <?= date('Y') ?> - [WEBSITE] - Design : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
	
	<?= $js ?>
</body>
</html>