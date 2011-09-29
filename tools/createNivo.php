<?php
/*
	Fonction createNivo
	# Créer une galerie nivoSlider à partir d'un fichier et
	# prépare le code JS correspondant
	
	@ Dépendances
	@ timthumb
	
	$path
		Chemin vers le dossier contenant les images
	$largeur
		Largeur de la galerie
	$hauteur
		Hauteur de la galerie
	$options
		Options nivoSlider à appliquer à la galerie
	$shuffle
		Mélange ou non l'ordre des images trouvées dans le dossier
*/
function createNivo($path, $largeur, $hauteur, $options = NULL, $shuffle = true)
{
	global $nivoSlider;
	
	if(!function_exists('findString')) include_once('cerberus/tools/findString.php');
	
	// Pourcentages
	if(findString('%', array($largeur, $hauteur)))
	{
		$largeur = str_replace('%', '', $largeur);
		$hauteur = str_replace('%', '', $hauteur);
	}
	elseif(findString('%', $largeur))
	{
		$largeur = str_replace('%', '', $largeur);
		$largeur = $hauteur * ($largeur / 100);
	}
	elseif(findString('%', $hauteur))
	{
		$hauteur = str_replace('%', '', $hauteur);
		$hauteur = $largeur * ($hauteur / 100);
	}

	echo '<div id="' .$path. '" style="height:' .$hauteur. 'px; max-width: ' .$largeur. 'px; margin:auto">';
	$arrayImages = glob('file/' .$path. '/*.jpg');
	if($shuffle) shuffle($arrayImages);
	foreach($arrayImages as $file)
	{
		$file = str_replace('file/', '', $file);
		echo '<img src="' .timthumb($file, $largeur, $hauteur). '" />';
	}
	echo '</div>';
		
	$nivoSlider[$path] = $options;
}
?>