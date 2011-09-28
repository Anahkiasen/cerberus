<?php
/*
	Fonction timthumb
	# Recadre et redimensionne une image
	
	$file
		Image de base, placée dans le dossier file/
	$width
		Largeur voulue
	$height
		Hauteur voulue
	$crop
		Recadrer ou non
		
	# La largeur ou la hauteur peuvent être en pourcentage, en quel
	# cas le paramètre en pourcentage représentera un pourcentage
	# de l'autre paramètre. Exemple, 100px de large et 50% de haut
	# donnera 50px de haut (50% de 100px)
*/
function timthumb($file, $width = '', $height = '', $crop = 1)
{
	// Tailles en pourcentages
	if(findString('%', $width))
	{
		$width = substr($width, 0 , -1);
		$width = $height * ($width / 100);
	}
	elseif(findString('%', $height))
	{
		$height = substr($height, 0 , -1);
		$height = $width * ($height / 100);
	}

	if(!empty($width)) $params['w'] = $width;
	if(!empty($height)) $params['h'] = $height;
	
	$params['zc'] = $crop;
	$params['s'] = 1;
	
	return 'file/timthumb.php?src=file/' .$file. '&' .simplode('=', '&', $params);
}
?>