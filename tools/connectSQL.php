<?php
/*
	Fonction connectSQL
	# Etablit un lien avec la base de données
	
	$localhost
		Nom de la base de données si travail en local
	$host
		Serveur SQL
	$user
		Login SQL
	$mdp
		Mot de passe SQL
	$db
		Base de donnée SQL
*/
function connectSQL($localhost = 'Maxime', $host = '', $user = '', $mdp = '', $db = '')
{
	if($_SERVER['HTTP_HOST'] == 'localhost:8888')
	{
		$MYSQL_HOST = 'localhost';
		$MYSQL_USER = 'root';
		$MYSQL_MDP = 'root';
		$MYSQL_DB = $localhost;
	}
	elseif($_SERVER['HTTP_HOST'] == 'the8day.info')
	{
		$MYSQL_HOST = 'db124.1and1.fr';
		$MYSQL_USER = 'dbo144396219';
		$MYSQL_MDP = 'naxam35741';
		$MYSQL_DB = 'db144396219';
	}
	else
	{
		$MYSQL_HOST = $host;
		$MYSQL_USER = $user;
		$MYSQL_MDP = $mdp;
		$MYSQL_DB = $db;
	}
		mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP) or die('La connexion au serveur SQL a &eacute;chou&eacute;');
		mysql_select_db($MYSQL_DB) or die('La connexion &agrave; la base de donn&eacute;es a &eacute;chou&eacute;');
		mysql_query("SET NAMES 'utf8'");
}
?>