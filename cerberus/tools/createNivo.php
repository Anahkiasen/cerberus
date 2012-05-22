<?php
/*
	Fonction createNivo
	# Créer une galerie nivoSlider à partir d'un fichier et
	# prépare le code JS correspondant
	
	@ Dépendances
	@ timthumb
	
	$path
		Chemin vers le dossier contenant les images et ID du block
	$largeur
		Largeur de la galerie
	$hauteur
		Hauteur de la galerie
	$options
		Options nivoSlider à appliquer à la galerie
	$shuffle
		Mélange ou non l'ordre des images trouvées dans le dossier
*/
function createNivo($path, $largeur, $hauteur, $options = NULL, $shuffle = TRUE)
{
	if(str::find('%', $largeur))
	{
		$largeur = str_replace('%', '', $largeur);
		$largeur = floor($hauteur * ($largeur / 100));
	}
	elseif(str::find('%', $hauteur))
	{
		$hauteur = str_replace('%', '', $hauteur);
		$hauteur = floor($largeur * ($hauteur / 100));
	}
	
	$idblock = str::slugify($path);

	echo '<div id="' .$idblock. '" style="height:' .$hauteur. 'px; max-width: ' .$largeur. 'px; margin:auto">'.PHP_EOL;
	$arrayImages = glob(PATH_FILE.$path. '/*.jpg');
	if($shuffle) shuffle($arrayImages);
	foreach($arrayImages as $file)
	{
		$file = str_replace(PATH_FILE, NULL, $file);
		echo str::img(media::timthumb($file, $largeur, $hauteur)).PHP_EOL;
	}
	echo '</div>'.PHP_EOL;
	
	dispatch::plugin('nivoSlider', '#'.$idblock, $options);
}
?>