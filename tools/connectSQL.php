<?php
/*
	Fonction connectSQL
	# Etablit un lien avec la base de données
	
	$LOCALHOST
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
function connectSQL()
{
	/* 
	########################################
	### DEFINITION DES IDENTIFIANTS SQL ####
	########################################
	*/
	
	// Chemin du fichier config
	$conf = 'cerberus/conf.php';
	
	// Récupération des identifiants du fichier config
	if(file_exists($conf))
	{
		include($conf);
		if(LOCAL)
		{
			// Si nous sommes en local
			//$MYSQL_HOST = $LOCAL_HOST;
			//$MYSQL_USER = $LOCAL_USER;
			//$MYSQL_MDP = $LOCAL_MDP;
			$MYSQL_DB = $LOCAL_DB;
		}
		else
		{
			// Si nous sommes en ligne
			$MYSQL_HOST = $PROD_HOST;
			$MYSQL_USER = $PROD_USER;
			$MYSQL_MDP = $PROD_MDP;
			$MYSQL_DB = $PROD_DB;
		}
	}
	
	// Trousseau d'accès
	if($_SERVER['HTTP_HOST'] == 'localhost:8888')
	{
		// Local MAMP
		$MYSQL_HOST = 'localhost';
		$MYSQL_USER = 'root';
		$MYSQL_MDP = 'root';
	}
	elseif($_SERVER['HTTP_HOST'] == '127.0.0.1')
	{
		// Local EasyPHP
		$MYSQL_HOST = 'localhost';
		$MYSQL_USER = 'root';
		$MYSQL_MDP = NULL;
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
		$MYSQL_HOST = 'hostingmysql51';
		$MYSQL_USER = '859841_maxime';
		$MYSQL_MDP = 'MAXSTA001';
	}
	
	/* 
	########################################
	########## CONNEXION SQL ###############
	########################################
	*/
	
	// Tentative de connexion à la base de données
	$isConnect = @mysql_connect($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP);
	$isDatabase = @mysql_select_db($MYSQL_DB);
	$isOnline = ($isConnect and $isDatabase);
	mysql_query("SET NAMES 'utf8'");
		
	// Si la connexion réussit, on sauvegarde le fichier config valide
	if($isOnline)
	{
		if(!file_exists($conf))
		{
			if(file_exists('cerberus/cache/')) mkdir('cerberus/cache/');
			if(LOCAL)
			{
				sfputs($conf, 
				"<?php
				\$LOCAL_HOST = '$MYSQL_HOST';
				\$LOCAL_USER = '$MYSQL_USER';
				\$LOCAL_MDP = '$MYSQL_MDP';
				\$LOCAL_DB = '$MYSQL_DB';
				?>");
			}
			else
			{
				sfputs($conf, 
				"<?php
				\$PROD_HOST = '$MYSQL_HOST';
				\$PROD_USER = '$MYSQL_USER';
				\$PROD_MDP = '$MYSQL_MDP';
				\$PROD_DB = '$MYSQL_DB';
				?>");
			}
		}
		
	/* 
	########################################
	########## BACKUP DE LA BASE ###########
	########################################
	*/
	
		// Sauvegarde et chargement de la base
		$tables_base = mysqlQuery('SHOW TABLES');
		$database_name = explode('_', $LOCAL_DB);
		
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
			 backupSQL($database);	
		}
		else if(file_exists('cerberus/cache/sql')) die('Une erreur est survenue durant la sauvegarde de la base de données');
	}
}
?>