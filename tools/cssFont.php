<?php
function cssFont($queryFont)
{
	if(!is_array($queryFont)) $queryFont = array($queryFont);
	$fonts = implode(':extralight,light,regular,bold|', $queryFont);
	$fonts = str_replace(' ', '+', $fonts);

	echo '<link href="http://fonts.googleapis.com/css?family=' .$fonts. ':light,regular,bold" rel="stylesheet" type="text/css">';
}
?>