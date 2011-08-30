<?php
function backupSQL($filename)
{
	$file = '';
	$path = 'cerberus/cache/sql/';
	
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
				echo 'La sauvegarde du ' .implode('-', $folderDate). ' a bien été supprimée<br />';
			}
		}
	}  
	
	// Récupération de la liste des tables
	$listeTables = array_values(mysqlQuery("SHOW TABLES"));
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
	
	// Création du dossier à la date si inexistant
	$folderName = $path.date('Y-m-d');
	if(!file_exists($folderName)) 
	{
		mkdir($folderName);
	
		// Création du fichier
		$filename = $filename. '-' .date('H-i-s'). '.sql';
		$filepath = $folderName. '/' .$filename;
		$monfichier = fopen($filepath, 'w+');
		fwrite($monfichier, pack("CCC", 0xef,0xbb,0xbf));
		fputs($monfichier, $file);
		fclose($monfichier);
		
		foreach($listeTables as $table) echo 'Sauvegarde de la table ' .$table. '<br />';
		echo 'Le fichier ' .$filename. ' a bien été crée.';
	}
	else echo 'Une sauvegarde existe déjà pour cette date.';
}
?>