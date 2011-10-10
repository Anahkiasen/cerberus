<?php
/*
	Fonction mysqlQuery
	# Récupère et traite des données mySQL en array exploitables
	# Execute des requêtes et affiche un message en conséquence
	
	$query
		Requête SQL à exécuter
		Peut être une chaîne [ $query ] ou un array contenant des messages [ array($query, $success, $failure) ]
		
		Si $query est un array, ce qui sous-entend une requête d'execution (DELETE, UPDATE, INSERT), la fonction affiche le message d'erreur/succès correspondant
		En cas d'erreur, si un message d'erreur est précisé, ce dernier est affiché dans le contexte du site
		Sinon l'affichage est interrompu via un exit(), et la requête est retournée ainsi que le message mysql_error();
		
		Si $query n'est pas un array, la fonction retourne un array() exploitable selon la syntaxe corresponsante		
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
	if(is_array($query))
	{
		$thisQuery = mysql_query($query[0]);
		if($thisQuery)
		{
			if(isset($query[1]))
				echo display($query[1]);
			return true;
		}
		else
		{
			if(isset($query[2])) echo display($query[2]);
			else
			{
				echo display(htmlentities($query)).mysql_error();
				exit(errorHandle('SQL', mysql_error(), __FILE__, __LINE__));
			}
			return false;
		}
	}
	else 
	{
		$thisQuery = mysql_query($query);
		if($thisQuery)
		{
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
						if($forceArray) return $fetchAssoc;
						else foreach($fetchAssoc as $value) return $value;
					}
					else
					{
						// UNIQUE RESULT - MULTIPLE FIELDS
						if($forceArray) return mysqlQuery_remapArray($fetchAssoc, $cle);
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
						if((isset($fetchAssoc[$cle]) and count($fetchAssoc) == 2) or (count($fetchAssoc) == 1))
						{
							// MULTIPLE RESULTS - UNIQUE FIELD
							if($forceArray) $returnArray = $returnArray + mysqlQuery_remapArray($fetchAssoc, $cle);
							else 
							{
								if(isset($fetchAssoc[$cle])) $thisKey = $fetchAssoc[$cle];
								foreach($fetchAssoc as $key => $value)
								{
									if(!isset($thisKey)) $thisKey = $value;
									if($key != $cle or count($fetchAssoc) == 1) $returnArray[$thisKey] = stripslashes($value);
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
		else
		{
			echo display(htmlentities($query)).mysql_error();
			exit(errorHandle('SQL', mysql_error(), __FILE__, __LINE__));
		}
	}
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
		if($key != $cle) $returnArray[$thisKey][$key] = stripslashes($value);
	}
	return $returnArray;
}
/*
	Fonction multiQuery
	# Execute un ensemble de requêtes via MySQLi
	
	$query
		Requête à exécuter
	$login
		Array contenant les informations de login SQL si non
		présentes dans l'environement en cours
*/
function multiQuery($query, $login = NULL)
{
	global $MYSQL_HOST;
	global $MYSQL_USER;
	global $MYSQL_MDP;
	global $MYSQL_DB;
	
	// Identifiants manuels
	if(!empty($login) and is_array($login))
		list($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB) = $login; 
	
	// Connexion
	$db = new mysqli($MYSQL_HOST, $MYSQL_USER, $MYSQL_MDP, $MYSQL_DB);
	$db->set_charset("utf8");

	if ($db->multi_query($query)) 
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