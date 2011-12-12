<?php
/*
	Fonction backupSQL
	# Effectue une sauvegarde de la base de donnée
	# Créer un dossier par date dans le dossier cerberus/cache/sql par défaut
	# Ne garde que les sauvegardes du mois en cours et celles du mois précédent
	# aux dates du 1er et du 15
	
	$filename
		Identifiant du fichier de sauvegarde - usuellement le nom de la base de données
*/
function backupSQL()
{	
	// Sauvegarde et chargement de la base
	$tables_base = db::showtables();
	if(empty($tables_base))
	{
		// Si la base de données est vide, chargement de dernière la sauvegarde
		foreach(glob('cerberus/cache/sql/*') as $file) 
			$fichier = $file;
			
		if(isset($fichier))
		{	
			$fichier = a::get(explode('/', $fichier), 3);
			
			foreach(glob('cerberus/cache/sql/' .$fichier. '/*.sql') as $file)
				$fichier = $file;
				
			multiQuery(file_get_contents($fichier), array(config::get('db.host'), config::get('db.user'), config::get('db.mdp'), config::get('db.name')));
		}
	}
	elseif(!empty($tables_base))
	{
		$filename = str::slugify(config::get('sitename', config::get('db.name')));

		// Définition du nom du dossier
		$path = 'cerberus/cache/sql/';
		$folderName = $path.date('Y-m-d');
			
		// Création du dossier à la date si inexistant
		if(!file_exists($folderName) and !empty($tables_base)) 
		{
			$tables_base = array_values($tables_base);
			
			// Suppression des sauvegardes inutiles
			foreach(glob($path. '*') as $file) 
			{
				if(is_dir($file))
				{
					$folderDate = explode('-', str_replace($path, '', $file));
					
					if($folderDate[0] != date('Y')) $unlink = true;
					elseif($folderDate[0] == date('Y') and (date('m') - $folderDate[1] > 1)) $unlink = true;
					elseif($folderDate[0] == date('Y') and (date('m') - $folderDate[1] == 1) and !in_array($folderDate[2], array(1, 15))) $unlink = true;
					
					if(isset($unlink))
					{
						f::remove($file);
						//echo 'La sauvegarde du ' .implode('-', $folderDate). ' a bien été supprimée<br />';
					}
				}
			} 
		
			// Récupération de la liste des tables
			$file = NULL;
			foreach($tables_base as $table)
			{
				$file .= "DROP TABLE IF EXISTS $table;\n";
				
				// Création de la table
				$table_create = mysql_fetch_array(mysql_query("SHOW CREATE TABLE $table"));
				$file .= $table_create[1].";\n";
				
				// Contenu de la table
				$table_content = mysql_query("SELECT * FROM $table");
				while($row = mysql_fetch_assoc($table_content))
				{
					$line_insert = "INSERT INTO $table (";
					$line_value = ") VALUES (";
					
					// Valeurs
					foreach($row as $field => $value)
					{
						$line_insert .= "`$field`, ";
						$line_value .= "'" .mysql_real_escape_string($value). "', ";
					}
					
					// Suppression du , en trop
					$line_insert = substr($line_insert, 0, -2);
					$line_value = substr($line_value, 0, -2);
					$file .= $line_insert.$line_value. ");\n";
				}
			}
		
			// Création du fichier
			$filename = $filename. '-' .date('H-i-s'). '.sql';
			f::write($folderName. '/' .$filename, $file);
			
			return 'Le fichier ' .$filename. ' a bien été crée<br />Tables : ' .implode(', ', $tables_base);
		}
		else return 'Une sauvegarde existe déjà pour cette date.';
	}
}
?>