<?php
/*
	Fonction timthumb
	# Recadre et redimensionne une image
	
	$file
		Image de base, placée dans le dossier PATH_FILE
	$width
		Largeur voulue
	$height
		Hauteur voulue
	$crop
		Recadrer ou non
	$quality
		Qualité de l'image
	$sharpen
		Renforcer la netteté ou non
*/
function timthumb($file, $width = NULL, $height = NULL, $crop = NULL, $quality = NULL, $sharpen = NULL)
{
	if(!empty($width)) $params['w'] = $width;
	if(!empty($height)) $params['h'] = $height;
	
	if(!is_null($crop)) $params['zc'] = $crop;
	if(!is_null($quality)) $params['q'] = $quality;
	if(!is_null($sharpen)) $params['s'] = $sharpen;

	$file = str_replace(PATH_FILE, NULL, $file);
	$file = (str::find('../', $file))
		? realpath(PATH_FILE.$file)
		: PATH_FILE.$file;

	return 'cerberus/class/svn.timthumb.php?src=' .$file. '&' .a::simplode('=', '&', $params);
}
?>