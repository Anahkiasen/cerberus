<?php
/*
	Fonction cssFont
	# Permet l'intégration aisée de polices via la propriété @font-face
	
	$queryFont
		Liste des polices voulues ; peut être un array [ array('police1', 'police2') ] ou une simple requête [ 'police' ]
	$googleFont
		Précise l'utilisation ou non du répertoire Google Fonts.
		TRUE	Aucune autre démarche n'est requise, mais les polices doivent être présentes dans le répertoire Google Fonts
		FALSE	Les polices, aux formats eot, woff et ttf, doivent se trouver dans le sous-dossier font du dossier css
*/
function cssFont($queryFont, $googleFont = true)
{
	if(!is_array($queryFont)) $queryFont = array($queryFont);
	
	if($googleFont == true)
	{
		// Polices Google
		$fonts = implode(':extralight,light,regular,bold|', $queryFont);
		$fonts = str_replace(' ', '+', $fonts);
	
		echo '<link href="http://fonts.googleapis.com/css?family=' .$fonts. ':light,regular,bold" rel="stylesheet" type="text/css" />';
	}
	else
	{
		// Polices @font-face
		echo '<style type="text/css">';
		foreach($queryFont as $value)
			echo '
			@font-face
			{
				font-family: \'' .$value. '\';
				src: url(\'css/font/' .strtolower($value). '.eot\');
				src: local(\'?\'), url(\'css/font/' .strtolower($value). '.woff\') format(\'woff\'), url(\'css/font/' .strtolower($value). '.ttf\') format(\'truetype\');
			}';
		echo '</style>';	
	}
}
?>