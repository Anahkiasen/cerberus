<?php
function createNivo($path, $largeur, $hauteur, $options = '', $shuffle = true)
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
	if($shuffle == TRUE) shuffle($arrayImages);
	foreach($arrayImages as $file)
		echo '<img src="file/timthumb.php?src=' .$file. '&w=' .$largeur. '&h=' .$hauteur. '&zc=1" />';
	echo '</div>';
		
	$nivoSlider[$path] = $options;
}
?>