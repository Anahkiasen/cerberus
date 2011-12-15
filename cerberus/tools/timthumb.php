<?php
/*
	Fonction timthumb
	# Recadre et redimensionne une image
	
	$file
		Image de base, plac�e dans le dossier assets/file/
	$width
		Largeur voulue
	$height
		Hauteur voulue
	$crop
		Recadrer ou non
	$quality
		Qualit� de l'image
	$sharpen
		Renforcer la nettet� ou non
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

	return 'cerberus/class/class.timthumb.php?src=' .$file. '&' .a::simplode('=', '&', $params);
}
?>