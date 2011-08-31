<?php
function backupSQL($filename)
{	
	$path = 'cerberus/cache/sql/';
	$folderName = $path.date('Y-m-d');
	$listeTables = mysqlQuery("SHOW TABLES");
		
	// Création du dossier à la date si inexistant
	if(!file_exists($folderName) and !empty($listeTables)) 
	{
		$listeTables = array_values($listeTables);
		mkdir($folderName);
		
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
					sunlink($file);
					//echo 'La sauvegarde du ' .implode('-', $folderDate). ' a bien été supprimée<br />';
				}
			}
		}  
	
		// Récupération de la liste des tables
		$file = '';
		foreach($listeTables as $table)
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
		sfputs($folderName. '/' .$filename, $file);
		
		return 'Le fichier ' .$filename. ' a bien été crée<br />Tables : ' .implode(', ', $listeTables);
	}
	else return 'Une sauvegarde existe déjà pour cette date.';
}
?>