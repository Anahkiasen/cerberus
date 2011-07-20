<?php
/*
	Fonction sfputs
	# Insère du contenu dans un fichier
	
	$file
		Fichier dans lequel placer le contenu
	$content
		Contenu à placer
*/
function sfputs($file, $content)
{
	$thisFile = fopen($file, 'w+');
	fputs($thisFile, $content);
	fclose($thisFile);
}
?>