<?php
function backupSQL($filename)
{	
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
				//echo 'La sauvegarde du ' .implode('-', $folderDate). ' a bien été supprimée<br />';
			}
		}
	}  
		
	// Création du dossier à la date si inexistant
	$folderName = $path.date('Y-m-d');
	if(!file_exists($folderName)) 
	{
		$file = '';
		mkdir($folderName);
	
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
	
		// Création du fichier
		$filename = $filename. '-' .date('H-i-s'). '.sql';
		sfputs($folderName. '/' .$filename, $file);
		
		return 'Le fichier ' .$filename. ' a bien été crée<br />Tables : ' .implode(', ', $listeTables);
	}
	else return 'Une sauvegarde existe déjà pour cette date.';
}
function loadSQL($sql)
{
	global $MYSQL_HOST;
	global $MYSQL_USER;
	global $MYSQL_MDP;
	global $MYSQL_DB;
	
	$db = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB);
	$db->set_charset("utf8");

	if ($db->multi_query($sql)) 
	{
		echo '<table>';
		while ($db->next_result())
		{
			if ($resultset = $db->store_result())
			{
				while ($record = $resultset->fetch_array(MYSQLI_BOTH))
				{
					echo 
					'<tr>
						<td>' .$record['title']. '</td>
						<td>' .$record[2]. '</td>
					</tr>';
				}
				$resultset->free();
			}
		}
		echo '</table>';
	}
	else echo $db->error. '<br />';
}
?>