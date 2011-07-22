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
$cerberus = new Cerberus(array(\'browserSelector\', \'cssFont\', \'connectSQL\', \'Desired\'));

// Page en cours
$navigation = array();
$desiredPage = new desired($navigation);
list($pageVoulue, $sousPageVoulue, $renderNav, $renderSubnav, $pageVoulueFile) = $desiredPage->desired;

// Connexon à la base
if(function_exists(\'connectSQL\')) 
	connectSQL([WEBDB]);
	
$thisAgent = browserSelector();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" class="<?= $thisAgent ?>">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>[WEBSITE]</title>
<? cssFont(array(\'Open Sans\')) ?>
<link href="css/styles.css" rel="stylesheet" type="text/css" />
</head>

<body>
	<div id="global">
		<div id="header"></div>
		<div id="corps"></div>
		<div id="footer">&copy;Copyright <?= date(\'Y\') ?> - [WEBSITE] - Design : <a href="http://www.stappler.fr/">Le Principe de Stappler</a></div>
	</div>
</body>
</html>';

$cssFile = 
'@import "cerberus.css";

body
{
	font-family: \'Open Sans\', Helvetica, Verdana, sans-serif;
	font-size: small;
}
/*
########################################
############# STRUCTURE ################
########################################
*/
#global
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
	font-size: x-small;
	text-align: center;
}';

$cerberus = 
'body
{
	-webkit-font-smoothing: antialiased;
	-moz-font-smoothing: antialiased;
}
.admin #navbar,
form input[type=submit],
fieldset legend,
table thead td,
table .additem { background-color: #0082b8; }
p.infoblock { background-color:#006699; }
/*
########################################
######### BALISES COMMUNES #############
########################################
*/
p.clear { clear:both }
p.infoblock
{
	background-image: url(overlay/noir-50.png);
	margin: 0;
	margin-bottom: 10px;
	width: 100%;
	color: white;
	text-align: center;
	padding: 5px;
}
.float-left
{
	float:left;
	margin-right: 10px;
}
.float-right
{
	float:right;
	margin-left: 10px;
}
#left,
#right
{
	margin: 10px 0;
	width: 50%;
	float: left;
	text-align: justify;
	
	-moz-box-sizing:border-box;
	-ms-box-sizing:border-box;
	-webkit-box-sizing:border-box;
	box-sizing:border-box;
}
#left p:first-child { margin-top: 0; }
#left { padding-right: 15px; }
/*
########################################
############ ADMIN ###############
########################################
*/
.admin #navbar
{
	text-align:center;
	padding: 5px 0;
	background-image: url(overlay/noir-50.png);
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
.admin #navbar a:hover { background-image: url(overlay/blanc-25.png); }
.admin #navbar a.hover { background-image: url(overlay/blanc-50.png); }
.admin table .additem td
{
	background-image: url(overlay/blanc-25.png);
	text-align: center;
}
.admin table .additem:hover td { background-image: url(overlay/blanc-50.png); }
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
table td
{
	padding: 3px;
	text-align:center;
}
table td:first-child { text-align:left }
table tr:hover { background-image: url(overlay/noir-10.png); }
table thead td,
table td.entete
{
	background-image: url(overlay/noir-25.png);
	color: white
}
table thead td a,
table td.entete a
{
	color: white;
	text-decoration:underline;
}
table td.additem
{
	background-image: url(overlay/blanc-25.png);
	text-transform:uppercase;
	padding: 3px;
	text-align:center;
}
table .additem:hover { background-image: url(overlay/blanc-50.png); }
table .additem a { color: white; }
/*
########################################
############# FORMULAIRES ##############
########################################
*/
form { width:99%; }
fieldset
{
	border:none;
	margin:auto;
	padding:0;
}
fieldset dd
{
	position:absolute;
	right:5px;
	top:5px;
	z-index:50;
}
fieldset dl
{
	background-color:#EEE;
	color:#666;
	margin:0;
	padding:13px 5px;
	position:relative;
	width:100%;
}
fieldset dl.actualThumb
{
	border:0 solid red;
	height:180px;
}
fieldset dl.actualThumb a
{
	background:url(overlay/noir-25.png);
	color:#FFF;
	padding:5px 30px;
}
fieldset dl.actualThumb a:hover { background:url(overlay/noir-50.png); }
fieldset dl.actualThumb a:active { background:url(overlay/noir-75.png); }
fieldset dl.actualThumb img { margin-bottom:10px; }
fieldset dl.actualThumb p
{
	background:url(overlay/noir-10.png);
	margin:10px;
	padding:10px 10px 15px;
}
fieldset dl.submit dd,
fieldset dl.submit.submit,
fieldset dl.submit.submit p
{
	margin:0;
	padding:0 5px;
	position:static;
}
fieldset dl.submit.submit:hover { background:#EEE; }
fieldset dl.textarea { height:200px; }
fieldset dl.textarea dd { width:60%; }
fieldset dl.textarea textarea
{
	height:190px;
	top:4px;
	width:97%;
}
fieldset dl:hover
{
	background-image:url(overlay/noir-10.png);
	background-repeat:repeat;
}
fieldset dl:hover input[type=text],
fieldset dl:hover input[type=password],
fieldset dl:hover select,
fieldset dl:hover textarea
{
	background-color:#555;
	border:4px solid #999;
	color:#FFF;
}
fieldset dt { margin-left:5px; }
fieldset label span.mandatory { color:red; }
input[type=submit]
{
	border:none;
	border-bottom:5px solid #67714f;
	color:#FFF;
	cursor:pointer;
	font-size:12px;
	margin:10px;
	padding:5px;
	text-align:center;
	width:80%;
}
input[type=submit]:hover { background-image:url(overlay/blanc-25.png); }
input[type=submit]:active
{
	background-image:url(overlay/blanc-50.png);
	border:none;
	margin-top:15px;
}
input[type=text],
input[type=password],
select,
textarea
{
	background-color:#CCC;
	border:4px solid #AAA;
	bottom:2px;
	font-family:Open sans;
	font-size:1em;
	font-weight:lighter;
	padding:2px;
	position:relative;
	width:250px;
}
legend
{
	background-image:url(overlay/noir-25.png);
	color:#FFF;
	font-size:medium;
	letter-spacing:-1px;
	padding:5px;
	width:100%;
}
select { min-width:262px; }
select.dateForm
{
	min-width:0;
	width:75px;
}';

	sfputs('../css/styles.css', $cssFile);
	sfputs('../css/cerberus.css', $cerberus);
	sfputs('../index.php', $indexFile);
}
else echo 'Cerberus déjà déployé';
?>