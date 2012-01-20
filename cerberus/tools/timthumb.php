<?php
/*
	Fonction timthumb
	# Recadre et redimensionne une image
	
	$file
		Image de base, placée dans le dossier assets/file/
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

	$file = (str::find('../', $file))
		? realpath('assets/file/' .$file)
		: 'assets/file/' .$file;

	return 'cerberus/class/svn.timthumb.php?src=' .$file. '&' .a::simplode('=', '&', $params);
}
?>