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
function timthumb($file, $width = NULL, $height = NULL, $params = array())
{
	if(!empty($width)) $params['w'] = $width;
	if(!empty($height)) $params['h'] = $height;
	
	if(!str::find('http', $file))
	{
		$file = str_replace(PATH_FILE, NULL, $file);
		$file = (str::find('../', $file))
			? realpath(PATH_FILE.$file)
			: PATH_FILE.$file;
	}
		
	return 'cerberus/class/svn.timthumb.php?src=' .$file. '&' .a::glue($params, '&', '=');
}
?>