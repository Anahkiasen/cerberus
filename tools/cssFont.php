<?php
/*
	Fonction cssFont
	# Permet l'int�gration ais�e de polices via la propri�t� @font-face
	
	$fonts
		Liste des polices voulues ; peut �tre un array [ array('police1' => 'light,medium', 'police2' => '200,400') ou array('police1', 'police2') ] ou une simple requ�te [ 'police' ]
	$googleFont
		Pr�cise l'utilisation ou non du r�pertoire Google Fonts.
		TRUE	Aucune autre d�marche n'est requise, mais les polices doivent �tre pr�sentes dans le r�pertoire Google Fonts
		FALSE	Les polices, aux formats eot, woff et ttf, doivent se trouver dans le sous-dossier font du dossier css
*/
function cssFont($fonts, $googleFont = true)
{
	if(!is_array($fonts)) $fonts = array($fonts);
	
	if($googleFont)
	{
		// Polices Google
		$fontString = NULL;
		foreach($fonts as $key => $value)
		{
			if(!empty($fontString)) $fontString .= '|';
			if(is_numeric($key)) $fontString .= $value. ':light,regular,bold';
			else $fontString .= $key. ':' .$value;
		}
		$fontString = str_replace(' ' , '+', $fontString);
	
		echo '<link href="http://fonts.googleapis.com/css?family=' .$fontString. '" rel="stylesheet" type="text/css" />';
	}
	else
	{
		// Polices @font-face
		echo '<style type="text/css">';
		foreach($fonts as $value)
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