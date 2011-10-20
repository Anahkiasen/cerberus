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
			<dt>Serveur SQL</dt>
			<dd><input type="text" name="sql-server" /></dd>
		</dl>
		<dl>
			<dt>Identifiant SQL</dt>
			<dd><input type="text" name="sql-login" /></dd>
		</dl>
		<dl>
			<dt>Mot de passe SQL</dt>
			<dd><input type="text" name="sql-mdp" /></dd>
		</dl>
		<dl>
			<dt>Base de donnée</dt>
			<dd><input type="text" name="sql-bdd" /></dd>
		</dl>
		<dl>
			<dt>Base de donnée locale</dt>
			<dd><input type="text" name="sql-local" /></dd>
		</dl>
	</fieldset>
	<dl class="submit"> 
		<dd><p style="text-align:center"><input type="hidden" name="submit" value="true" /><input type="submit" value="Valider" /></p></dd> 
	</dl>
</form>
<?php
if(!file_exists('../../index.php'))
{	
	foreach(glob('cerberus/core/*.php') as $file) require_once($file);

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
		include('../tools/display.php');
		include('../tools/mysqlQuery.php');
		
		// Données SQL
		if(isset($_POST['sql-local']))
		{
			$localhost = $_POST['sql-local'];
	
			if(isset($_POST['sql-server'], $_POST['sql-login'], $_POST['sql-mdp']))
			{
				$MYSQL_HOST = $_POST['sql-server'];
				$MYSQL_USER = $_POST['sql-login'];
				$MYSQL_MDP = $_POST['sql-mdp'];
				$MYSQL_DB = $_POST['sql-bdd'];
			}
			else
			{
				$MYSQL_HOST = "";
				$MYSQL_USER = "";
				$MYSQL_MDP = "";
				$MYSQL_DB = "";
			}
			
			mkdir('../cache/');
			mkdir('../cache/sql/');
			
			// Deprecated
			f::write('../conf.php',
			"<?php
			// Environnement
			\$PRODUCTION = FALSE;
			\$REWRITING = FALSE;
			\$LANGUES = FALSE;
			
			// MySQL
			\$LOCAL_DB = '$localhost';
			\$PROD_HOST = '$MYSQL_HOST';
			\$PROD_USER = '$MYSQL_USER';
			\$PROD_MDP = '$MYSQL_MDP';
			\$PROD_DB = '$MYSQL_DB';
			?>");
			
			db::connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB);
		}
		
		// Table langue
		if($_POST['site-multi'])
		{
			mysql_query('DROP TABLE IF EXISTS `langue`;');
			mysqlQuery(array('CREATE TABLE IF NOT EXISTS `langue` (
			  `tag` varchar(255) NOT NULL,
			  `fr` varchar(255) NOT NULL,
			  PRIMARY KEY (`tag`)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;'));	
			mysqlQuery(array('INSERT INTO langue VALUES ("menu-home", "Accueil")'));
		}
		
		// Table ADMIN
		mysql_query('DROP TABLE IF EXISTS `admin`;');
		mysqlQuery(array('CREATE TABLE IF NOT EXISTS `admin` (
		  `user` varchar(32) NOT NULL,
		  `password` varchar(32) NOT NULL,
		  PRIMARY KEY (`user`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1'));
		
		// Table LOGS
		mysql_query('DROP TABLE IF EXISTS `logs`;');
		mysqlQuery(array('CREATE TABLE IF NOT EXISTS `logs` (
		  `ip` varchar(20) NOT NULL,
		  `date` datetime NOT NULL,
		  `platform` varchar(10) NOT NULL,
		  `browser` varchar(10) NOT NULL,
		  `version` varchar(10) NOT NULL,
		  `engine` varchar(10) NOT NULL,
		  `mobile` enum(\'0\',\'1\') NOT NULL,
		  PRIMARY KEY (`ip`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;'));
		
		// Table META
		mysql_query('DROP TABLE IF EXISTS `meta`;');
		mysqlQuery(array('CREATE TABLE `meta` (
		  `page` varchar(30) NOT NULL,
		  `titre` varchar(200) NOT NULL,
		  `description` text NOT NULL,
		  `url` varchar(150) NOT NULL,
		  `langue` enum(\'fr\',\'en\') NOT NULL,
		  PRIMARY KEY (`page`)
		) ENGINE=MyISAM DEFAULT CHARSET=latin1;'));
		
		// Création des dossiers
		mkdir('../../css/');
		if(isset($_POST['site-tree']))
		{
			mkdir('../../pages/');
			f::write('../../pages/home-home.html', '');
		}
		if(isset($_POST['file-js'])) mkdir('../../js/');
		if(isset($_POST['file-file'])) mkdir('../../file/');
		copydir('min', '../../min');
			
		// Déplacement des fichiers CSS et PHP
		copy('styles.css', '../../css/styles.css');
		copy('mail.css', '../../css/mail.css');
		copy('img/delete.png', '../../css/delete.png');
		copy('img/edit.png', '../../css/edit.png');
		copy('img/load.png', '../../css/load.png');
		
		copy('cerberus.css', '../../css/cerberus.css');
		copydir('overlay', '../../css/overlay');
		
		$index = file_get_contents('main.php');
		$index = str_replace('[BDD]', "'" .$MYSQL_DB. "'", $index);
		f::write('../../index.php', $index);
		
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