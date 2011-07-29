<?php
function timthumb($file, $width = '', $height = '', $crop = 1)
{
	global $productionMode;
	
	if($productionMode == true) return 'file/' .$file;
	else
	{
		if(!empty($width)) $params['w'] = $width;
		if(!empty($height)) $params['h'] = $height;
		$params['zc'] = $crop;
		
		return 'file/timthumb.php?src=file/' .$file.implode('&', $params);
	}
}
?>