<?php
/*
	Fonction sfputs
	# Ins�re du contenu dans un fichier
	
	$file
		Fichier dans lequel placer le contenu
	$content
		Contenu � placer
*/
function sfputs($file, $content)
{
	$dossier = dirname($file);
	if(!file_exists($dossier))
	{
		if(!mkdir($dossier, 0700, true))
			echo 'Impossible de cr�er le dossier';
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