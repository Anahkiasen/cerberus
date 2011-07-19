<?php
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