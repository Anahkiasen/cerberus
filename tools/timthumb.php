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
*/
function timthumb($file, $width = NULL, $height = NULL, $crop = 1)
{
	if(!empty($width)) $params['w'] = $width;
	if(!empty($height)) $params['h'] = $height;
	
	$params['zc'] = $crop;
	$params['s'] = 1;
	
	$file = (strpos($file, '../') !== FALSE)
		? realpath('assets/file/' .$file)
		: 'assets/file/' .$file;
		
	return 'cerberus/class/class.timthumb.php?src=' .$file. '&' .a::simplode('=', '&', $params);
}
?>