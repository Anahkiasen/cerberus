<?php
// Chargement du moteur Cerberus
include_once('cerberus/init.php');
$cerberus = new Cerberus(array('browserSelector', 'cssFont', '[sql]', 'createIndex', 'Desired'));
$index = createIndex();

// Connexon à la base
if(function_exists('connectSQL')) 
	connectSQL([WEBDB]);

// Arbre de navigation et page en cours
$navigation = array();
$desiredPage = new desired($navigation);
list($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav, $pageVoulueFile) = $desiredPage->desired;

// Dispatch des fonctions et API
$cerberus->cerberusDispatch(), $pageVoulue.$sousPageVoulue);

list($css, $js, $thisScripts) = $cerberus->cerberusAPI(array(), $pageVoulue.$sousPageVoulue);
	
$thisAgent = browserSelector();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="<?= $thisAgent ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>[WEBSITE]</title>
<? cssFont(array('Open Sans')) ?>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div id="global">
		<div id="header"></div>
		<div id="corps"></div>
		<div id="footer">&copy;Copyright <?= date('Y') ?> - [WEBSITE] - Design : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
</body>
</html>