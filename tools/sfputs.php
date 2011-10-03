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
	$dossier = dirname($file);
	if(!file_exists($dossier))
	{
		if(!mkdir($dossier, 0700, true))
			echo 'Impossible de créer le dossier';
		else sfputs($file, $content);
	}
	else
	{
		$thisFile = fopen($file, 'w+');
		fputs($thisFile, $content);
		fclose($thisFile);
	}
}
?>