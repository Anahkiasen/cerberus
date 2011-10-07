<?php
/*
	Fonction sexist
	# Vérifie si un fichier existe, sinon retourne NULL
	
	$file
		Le fichier à chercher
*/
function sexist($file)
{
	return (file_exists($file)) ? $file : NULL;
}
?>