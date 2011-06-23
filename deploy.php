<?php
if(!file_exists('../index.php'))
{
	include('tools/sfputs.php');
	
	mkdir('../css/');
	mkdir('../include/');
	
$indexFile = 
'<?php
include_once(\'cerberus/init.php\');
$cerberus = new Cerberus(array(\'browserSelector\', \'desiredPage\', \'connectSQL\'));

// Page en cours
$navigation = array();
$pageVoulue = desiredPage($navigation);

// Connexon à la base
if(function_exists(\'connectSQL\')) 
	connectSQL([WEBDB]);
	
$thisAgent = browserSelector();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="<?= $thisAgent ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>[WEBSITE]</title>
<? cssFont(array(\'Open Sans\')) ?>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div id="header"></div>
	<div id="corps"></div>
	<div id="footer">&copy;Copyright <?= date(\'Y\') ?> - [WEBSITE] - Design : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
</body>
</html>';

	sfputs('../css/styles.css', '');
	sfputs('../index.php', $indexFile);
}
else echo 'Cerberus déjà déployé';
?>