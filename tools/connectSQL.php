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
	// Nom du fichier config
	$conf = 'cerberus/cache/conf.php';
	
	// Connexion à partir du fichier config
	if(file_exists($conf)) include($conf);	
	else
	{
		// Manuel
		$MYSQL_HOST = $host;
		$MYSQL_USER = $user;
		$MYSQL_MDP = $mdp;
		$MYSQL_DB = $db;
	}
	
	if($_SERVER['HTTP_HOST'] == 'localhost:8888')
	{
		// Local MAMP
		$MYSQL_HOST = 'localhost';
		$MYSQL_USER = 'root';
		$MYSQL_MDP = 'root';
		$MYSQL_DB = $localhost;
	}
	elseif($_SERVER['HTTP_HOST'] == '127.0.0.1')
	{
		// Local EasyPHP
		$MYSQL_HOST = 'localhost';
		$MYSQL_USER = 'root';
		$MYSQL_MDP = '';
		$MYSQL_DB = $localhost;
	}
	elseif($_SERVER['HTTP_HOST'] == 'the8day.info')
	{
		// Le Huitième Jour
		$MYSQL_HOST = 'db124.1and1.fr';
		$MYSQL_USER = 'dbo144396219';
		$MYSQL_MDP = 'naxam35741';
		$MYSQL_DB = 'db144396219';
	}
	elseif($_SERVER['HTTP_HOST'] == 'stappler.fr' or $_SERVER['HTTP_HOST'] == 'www.stappler.fr')
	{
		// Stappler
		$database = explode('_', $localhost);
		
		$MYSQL_HOST = 'hostingmysql51';
		$MYSQL_USER = '859841_maxime';
		$MYSQL_MDP = 'MAXSTA001';
		$MYSQL_DB = 'stappler_fr_' .$database[1];
	}
	
	$isConnect = @mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP);
	$isDatabase = @mysql_select_db($MYSQL_DB);
	$isOnline = ($isConnect and $isDatabase);
	mysql_query("SET NAMES 'utf8'");
		
	// Si toujours pas de connexion, affichage des erreurs
	if(!$isOnline)
	{
		unlink($conf);
		if(!$isConnect) die('La connexion au serveur ' .$MYSQL_HOST. ' via ' .$MYSQL_USER. '@' .$MYSQL_MDP. ' a &eacute;chou&eacute;');
		if(!$isDatabase) die('La connexion à la base de données ' .$MYSQL_DB. ' a échoué');
	}
	else
	{
		// Enregistrement du fichier CONF
		if(!file_exists($conf))
		{
			if(file_exists('cerberus/cache/')) sfputs($conf, 
			"<?php
			\$MYSQL_HOST = '$MYSQL_HOST';
			\$MYSQL_USER = '$MYSQL_USER';
			\$MYSQL_MDP = '$MYSQL_MDP';
			\$MYSQL_DB = '$MYSQL_DB';
			?>");
		}
	
		// Sauvegarde et chargement de la base
		$tables_base = mysqlQuery('SHOW TABLES');
		$database_name = explode('_', $localhost);
		$database = (isset($database_name[1]))
			? $database_name[1]
			: $database_name;
		if(is_array($database)) $database = $database[0];
	
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
					
				multiQuery(file_get_contents($fichier), array($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB));
			}
		}
		elseif(!empty($tables_base) and function_exists('backupSQL'))
		{
			// Si tout va bien, on effectue une sauvegarde
			 if($localhost != 'Maxime') backupSQL($database);	
		}
		else if(file_exists('cerberus/cache/sql')) die('Une erreur est survenue durant la sauvegarde de la base de données');
	}
}
?>