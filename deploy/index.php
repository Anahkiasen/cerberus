<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Déploiement de Cerberus</title>
<link href='http://fonts.googleapis.com/css?family=Open+Sans|Oswald' rel='stylesheet' type='text/css'>
<link href="cerberus.css" rel="stylesheet" type="text/css" />
<style type="text/css">
form
{
	width: 500px;
	margin:auto;
}
h1
{
	font-family: Oswald;
	text-align:center;
	text-transform:uppercase;
	font-size: 60px;
	font-weight: bold;
	color: #666;
	letter-spacing: -2px;
	margin: 5px 0;
}
</style>
</head>

<body>
<h1>Déploiement de Cerberus</h1>
<form method="post">
	<fieldset>
		<legend>Type de site</legend>
		<dl>
			<dt>Site multilingue</dt>
			<dd><input type="checkbox" name="site-multi" checked="checked" /></dd>
		</dl>
		<dl>
			<dt>Site à arborescence<br /><em>(Le site fait plus d'une page)</em></dt>
			<dd><input type="checkbox" name="site-tree" checked="checked" /></dd>
		</dl>
	</fieldset>
	<fieldset>
		<legend>Dossiers à créer</legend>
		<dl>
			<dt>Dossier <em>file</em><br />Contient les fichiers images</dt>
			<dd><input type="checkbox" name="file-file" checked="checked" /></dd>
		</dl>
		<dl>
			<dt>Dossier <em>js</em></dt>
			<dd><input type="checkbox" name="file-js" /></dd>
		</dl>
	</fieldset>
	<fieldset>
		<legend>Modules à précharger</legend>
		<dl>
			<dt>Activer jQuery</dt>
			<dd><input type="checkbox" name="module-jquery" /></dd>
		</dl>
		<dl>
			<dt>Activer jQueryUI</dt>
			<dd><input type="checkbox" name="module-jqueryui" /></dd>
		</dl>
		<dl>
			<dt>Activer SWFobject</dt>
			<dd><input type="checkbox" name="module-swfobject" /></dd>
		</dl>
		<dl>
			<dt>Mise en cache des ressources</dt>
			<dd><input type="checkbox" name="module-cache" /></dd>
		</dl>
	</fieldset>
	<fieldset>
		<legend>Informations SQL</legend>
		<dl>
			<dt>Base de donnée locale</dt>
			<dd><input type="text" name="sql-bdd" /></dd>
		</dl>
	</fieldset>
	<dl class="submit"> 
		<dd><p style="text-align:center"><input type="hidden" name="submit" value="true" /><input type="submit" value="Valider" /></p></dd> 
	</dl>
</form>
<?php
if(!file_exists('../../index.php'))
{	
	if(isset($_POST['submit']))
	{
		// Fonctions moteur
		function copydir($source, $destination)
		{
			$dir = opendir($source); 
			@mkdir($destination); 
			
			while(false !== ($file = readdir($dir)))
			{
				if(($file != '.') && ($file != '..')) 
				{
					if(is_dir($source. '/' .$file)) copydir($source. '/' . $file, $destination. '/' .$file); 
					else copy($source. '/' .$file, $destination. '/' .$file); 
				} 
			} 
			
			closedir($dir); 
		} 
		include('../tools/sfputs.php');
		
		// Création des dossiers
		mkdir('../../css/');
		if(isset($_POST['site-tree']))
		{
			mkdir('../../pages/');
			sfputs('../../pages/home-home.html', '');
		}
		if(isset($_POST['file-js'])) mkdir('../../js/');
		if(isset($_POST['file-file'])) mkdir('../../file/');
		mkdir('../cache/');
		mkdir('../cache/sql/');
	
		// Déplacement des fichiers CSS et PHP
		copy('styles.css', '../../css/styles.css');
		copy('mail.css', '../../css/mail.css');
		copy('img/cross.png', '../../css/cross.png');
		copy('img/pencil.png', '../../css/pencil.png');
		
		copy('cerberus.css', '../../css/cerberus.css');
		copydir('overlay', '../../css/overlay');
		if(isset($_POST['file-file'])) copy('timthumb.php', '../../file/timthumb.php');
		copy('main.php', '../../index.php');
		if(isset($_POST['module-cache'])) copy('n.htaccess', '../../n.htaccess');
		
		echo 'Cerberus correctement d&eacute;ploy&eacute;';
	}
}
else echo 'Cerberus d&eacute;j&agrave; d&eacute;ploy&eacute;';
?>

<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
<script type="text/javascript">
$("dl").click(function()
{
	isChecked = $("input", this).attr('checked');
	if(isChecked == "checked")
	{
		$('input[type=checkbox]', this).attr('checked', false);
		$(this).toggleClass('check');
	}
	else
	{
		$(this).toggleClass('check');
		$('input', this).attr('checked', true);
	}
});
</script>
</body>
</html>