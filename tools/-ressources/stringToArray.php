<?php
/* ####################################
############ STRING ; EN ARRAY ########
####################################### */
function stringToArray($string, $sort = TRUE)
{
	// Explosion de l'array
	$explode = explode(';', $string);
	foreach($explode as $value)
	{
		if(!isset($key)) $key = $value;
		else
		{
			$array[$key] = $value;
			unset($key);
		}
	}
	
	// Tri des valeurs
	if($sort == TRUE) ksort($array);
	
	return $array;
}
?>