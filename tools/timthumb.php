<?php
function timthumb($file, $width = '', $height = '', $crop = 1, $mode = '')
{
	global $productionMode;
	if($mode === '') $mode = $productionMode;
	
	if($mode == true) return 'file/' .$file;
	else
	{
		if(!empty($width)) $params['w'] = $width;
		if(!empty($height)) $params['h'] = $height;
		$params['zc'] = $crop;
		
		return 'file/timthumb.php?src=file/' .$file. '&' .simplode('=', '&', $params);
	}
}
?>