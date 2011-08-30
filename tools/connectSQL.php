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
	elseif($_SERVER['HTTP_HOST'] == 'stappler.fr' or $_SERVER['HTTP_HOST'] == 'www.stappler.fr')
	{
		$database = explode('_', $localhost);
		
		$MYSQL_HOST = 'hostingmysql51';
		$MYSQL_USER = '859841_maxime';
		$MYSQL_MDP = 'MAXSTA001';
		$MYSQL_DB = 'stappler_fr_' .$database[1];
	}
	else
	{
		$MYSQL_HOST = $host;
		$MYSQL_USER = $user;
		$MYSQL_MDP = $mdp;
		$MYSQL_DB = $db;
	}
		mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP) or die('La connexion au serveur ' .$MYSQL_HOST. ' via ' .$MYSQL_USER. '@' .$MYSQL_MDP. ' a &eacute;chou&eacute;');
		mysql_select_db($MYSQL_DB) or die('La connexion &agrave; la base de donn&eacute;es ' .$MYSQL_DB. ' a &eacute;chou&eacute;');
		mysql_query("SET NAMES 'utf8'");
	
		// Sauvegarde et chargement de la base
		$tables_base = mysqlQuery('SHOW TABLES');
		$database_name = explode('_', $localhost);
		$database = (isset($database_name[1]))
			? $database_name[1]
			: $database_name;

		if(empty($tables_base))
		{
			// Si la base de données est vide, chargement de dernière la sauvegarde
			foreach(glob('cerberus/cache/sql/*') as $file)  
				$fichier = $file;
				
			if(isset($fichier))
			{	
				$fichier = explode('/', $fichier);
				$fichier = $fichier[3];
				
				foreach(glob('cerberus/cache/sql/' .$fichier. '/*.sql') as $file)
					$fichier = $file;
					
				loadSQL(file_get_contents($fichier), array($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB));
			}
		}
		elseif(!empty($tables_base) and function_exists('backupSQL'))
		{
			// Si tout va bien, on effectue une sauvegarde
			backupSQL($database);	
		}
		else die('Une erreur est survenue lors du chargement de la base de données');
}
?>