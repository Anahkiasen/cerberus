<?php
/*
	Fonction simplode
	# Implode un array via plusieurs glues
	
	$glue1
		Vient se placer autour de la valeur
		Si glue1 est un array, il est possible de définir
		une chaîne à placer AVANT et APRES la valeur
	$glue2
		Vient se placer entre les entrées de l'array
	$array
		Array à imploser
	$convert
		TRUE	Passe les valeurs à la fonction bdd()
		FALSE	Laisse les valeurs intactes
*/
function simplode($glue1, $glue2, $array, $convert = true)
{
	if(is_array($array))
	{
		if(empty($glue2))
		{
			// WIP
		}
		else
		{
			$plainedArray = array();
			foreach($array as $key => $value)
			{	
				$value = ($convert) ? bdd($value) : $value;
				if(is_array($glue1)) $plainedArray[] = $key.$glue1[0].$value.$glue1[1];
				else $plainedArray[] = $key.$glue1.$value;
			}
			return implode($glue2, $plainedArray);
		}
	}
}
?>