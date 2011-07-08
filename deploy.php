<?php
if(!file_exists('../index.php'))
{
	include('tools/sfputs.php');
	
	mkdir('../css/');
	mkdir('../include/');
	mkdir('cache/');
	
$indexFile = 
'<?php
include_once(\'cerberus/init.php\');
$cerberus = new Cerberus(array(\'browserSelector\', \'cssFont\', \'connectSQL\', \'desiredPage\'));

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
	<div id="wrapper">
		<div id="header"></div>
		<div id="corps"></div>
		<div id="footer">&copy;Copyright <?= date(\'Y\') ?> - [WEBSITE] - Design : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
</body>
</html>';

$cssFile = 
'@import "tableForm.css";

body
{
	font-family: \'Open Sans\', Helvetica, Verdana, sans-serif;
	font-size: small;
	-webkit-font-smoothing: antialiased;
	-moz-font-smoothing: antialiased;
}
/*
########################################
############# STRUCTURE ################
########################################
*/
#wrapper
{
	margin:0 auto;
	width:960px;
}
#header
{

}
#corps
{

}
#footer
{
	text-align: center;
}
/*
########################################
############# BALISES ##################
########################################
*/
p.clear { clear:both }
p.infoblock
{
	background-color: #e3004f;
	margin: 0;
	margin-botom: 10px;
	width: 100%;
	color: white;
	text-align: center;
	padding: 5px;
}';

$cssTable = 
'/*
########################################
############ ADMIN ###############
########################################
*/
.admin #navbar
{
	text-align:center;
	padding: 5px 0;
	background-color: #006699;
	width: 100%;
	margin: 0 auto;
}
.admin #navbar a
{
	padding: 5px 10px;
	color: white;
	filter: none;
	text-shadow: none;
	text-transform: uppercase;
}
.admin #navbar a:hover { background-image: url(overlay-blanc-25.png); }
.admin #navbar a.hover { background-image: url(overlay-blanc-50.png); }
.admin table .additem td
{
	background-image: url(overlay-blanc-25.png);
	text-align: center;
}
.admin table .additem:hover td { background-image: url(overlay-blanc-50.png); }
.admin table .additem td a:hover { color: white; }
.admin #contenu table .additem td a { color: white; }
.admin dl.textarea textarea
{
	width: 97%;
	height: 290px;
	top: 4px;
}
.admin dl.textarea { height: 300px; }
.admin dl.textarea dd { width: 70%; }
/*
########################################
############# TABLEAUX #################
########################################
*/
table
{
	width: 100%;
	background: #EEE;
}
table td { padding: 3px; }
table tr:hover { background-image: url(overlay-noir-10.png); }
table thead td
{
	background-image: url(overlay-noir-25.png);
	color: white
}
/*
########################################
############# FORMULAIRES ##############
########################################
*/
form { width: 99% }
fieldset
{
	border: none;
	padding: 0;
	margin:auto;
}
input[type=text],
input[type=password],
select,
textarea
{
	border: 0;
	background-color: #CCC;
	border: 4px solid #AAA;
	padding: 2px;
	position:relative;
	bottom: 2px;
	width: 250px;
	font-size: 1em;
}
select { min-width: 262px; }
.submit dd,
.submit,
.submit p
{
	position: static;
	margin: 0;
	padding: 0 5px;
}
.submit:hover { background: #EEE }
form input[type=submit]
{
	width: 80%;
	background-color: #006699;
	border: none;
	font-size: 12px;
	color: white;
	padding: 5px;
	text-align:center;
	cursor:pointer;
	margin: 10px;
	border-bottom: 5px solid #005580;
}
form input[type=submit]:active
{
	margin-top: 15px;
	background:#0099FF;
	border: none;
}
form input[type=submit]:hover { background-color: #008bcc }
fieldset legend
{
	background-color: #7c885f;
	color: white;
	letter-spacing: -1px;
	font-size: medium;
	padding: 5px;
	width: 100%;
}
fieldset dt { margin-left: 5px; }
fieldset dl
{
	background-color: #EEE;
	color: #666;
	margin: 0;
	padding: 10px 5px;
	position: relative;
	width: 100%;
}
fieldset dl:hover
{
	background-image: url(overlay-noir-10.png);
	background-repeat:repeat;
}
fieldset dl:hover input[type=text],
fieldset dl:hover input[type=password],
fieldset dl:hover select,
fieldset dl:hover textarea
{
	background-color: #555;
	border: 4px solid #999;
	color:white;
}
fieldset dd
{
	position: absolute;
	right: 5px;
	top: 5px;
	z-index: 50;
}
fieldset dl.textarea textarea
{
	width: 97%;
	height: 190px;
	top: 4px;
}
fieldset dl.textarea { height: 200px; }
fieldset dl.textarea dd { width: 60%; }
select.dateForm
{
	min-width: 0px;
	width: 75px;
}';

	sfputs('../css/styles.css', $cssFile);
	sfputs('../css/tableForm.css', $cssTable);
	sfputs('../index.php', $indexFile);
}
else echo 'Cerberus déjà déployé';
?>