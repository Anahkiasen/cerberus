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
	$thisFile = fopen($file, 'w+');
	fputs($thisFile, $content);
	fclose($thisFile);
}
?>