<?php
/*
	Fonction mysqlQuery
	# Récupère et traite des données mySQL en array exploitables
	
	$query
		Requête SQL à exécuter
	$forceArray
		TRUE	Force la division des résultats selon une cle
		FALSE	Laisse les variables GET
	$cle 
		Clé selon laquelle indexer l'array de retour ; par défaut ID
		
	# Syntaxe des différents mode de la fonction
		UNIQUE RESULT - UNIQUE FIELD - FORCE[TRUE]			array($key => $value)
		UNIQUE RESULT - UNIQUE FIELD - FORCE[FALSE]			$value
		UNIQUE RESULT - MULTIPLE FIELDS - FORCE[TRUE]		array($cle => array($key => $value, $key => $value))
		UNIQUE RESULT - MULTIPLE FIELDS - FORCE[FALSE]		array($key => $value, $key => $value)
		MULTIPLE RESULTS - UNIQUE FIELD - FORCE[TRUE]		array($cle => array($key => $value, $key => $value))
		MULTIPLE RESULTS - UNIQUE FIELD - FORCE[FALSE]		array($cle => $value, $cle => $value)
		MULTIPLE RESULTS - MULTIPLE FIELDS - FORCE[TRUE]	array($cle => array($key => $value, $key => $value))
		MULTIPLE RESULTS - MULTIPLE FIELDS - FORCE[FALSE]	array($cle => array($key => $value, $key => $value))
		
*/
function mysqlQuery($query, $forceArray = FALSE, $cle = 'id')
{
	// Traitement de la requête
	$thisQuery = mysql_query($query) or exit(mysql_error());
	
	// Présence de résultats ou non
	if(mysql_num_rows($thisQuery) != 0)
	{
		// UNIQUE RESULT
		if(mysql_num_rows($thisQuery) == 1)
		{
			$fetchAssoc = mysql_fetch_assoc($thisQuery);
			if(count($fetchAssoc) == 1)
			{
				// UNIQUE RESULT - UNIQUE FIELD
				if($forceArray == TRUE) return $fetchAssoc;
				else foreach($fetchAssoc as $value) return $value;
			}
			else
			{
				// UNIQUE RESULT - MULTIPLE FIELDS
				if($forceArray == TRUE) return mysqlQuery_remapArray($fetchAssoc, $cle);
				else
				{
					foreach($fetchAssoc as $key => $value)
						$returnArray[$key] = $value;
					return $returnArray;
				}
			}
		}
		else
		{
			// MULTIPLE RESULTS
			$returnArray = array();
			while($fetchAssoc = mysql_fetch_assoc($thisQuery))
			{
				if(count($fetchAssoc) == 1)
				{
					// MULTIPLE RESULTS - UNIQUE FIELD
					if($forceArray == TRUE) $returnArray = $returnArray + mysqlQuery_remapArray($fetchAssoc, $cle);
					else 
					{
						if(isset($fetchAssoc[$cle])) $thisKey = $fetchAssoc[$cle];
						foreach($fetchAssoc as $key => $value)
						{
							if(!isset($thisKey)) $thisKey = $value;
							if($key != $cle) $returnArray[$thisKey] = $value;
						}
					}
				}
				else $returnArray = $returnArray + mysqlQuery_remapArray($fetchAssoc, $cle); // MULTIPLE RESULTS - MULTIPLE FIELDS
				unset($thisKey);
			}
			return $returnArray;
		}
	}
	else return FALSE;
}
/*
	Fonction mysqlQuery_remapArray Extends mysqlQuery
	# Transpose une suite de résultats en un tableau associatif unique
	
	$array
		Tableau à transposer
	$cle
		Clé de la fonction principale
*/
function mysqlQuery_remapArray($array, $cle)
{
	if(isset($array[$cle])) $thisKey = $array[$cle];
	foreach($array as $key => $value)
	{
		if(!isset($thisKey)) $thisKey = $value;
		if($key != $cle) $returnArray[$thisKey][$key] = $value;
	}
	return $returnArray;
}
?>