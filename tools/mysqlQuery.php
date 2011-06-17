<?php
function mysqlQuery($query, $cle = 'id', $forceArray = FALSE)
{
	// Traitement de la requête
	$thisQuery = mysql_query($query) or die(mysql_error());
	if(mysql_num_rows($thisQuery) != 0)
	{
		if(mysql_num_rows($thisQuery) == 1 and $forceArray == FALSE)
		{
			// Champ unique ou non
			$return = mysql_fetch_assoc($thisQuery);
			if(count($return) == 1) foreach($return as $key => $value) return $value;
			else return $return;
		}
		else
		{
			// Tableau multidimensionnel
			while($thisRows = mysql_fetch_assoc($thisQuery))
			{
				if(count($thisRows) == 1) foreach($thisRows as $key => $value) $array[] = $value;
				else
				{
					// Si la clé demandé existe
					if(isset($thisRows[$cle]))
					{
						foreach($thisRows as $key => $value)
						{
							if($key == $cle)
							{
								$id = $value;
								$array[$id] = array();
							}
							else 
							{
								// Tableau direct ou multi
								if(count($thisRows) == 2) $array[$id] = $value;
								else $array[$id][$key] = $value;
							}
						}
					}
					else foreach($thisRows as $key => $value) $array[] = $value;
				}
			}
			return $array;
		}
	}
	else return FALSE;
}
?>