<?php
function swf($swf, $bloc, $width, $height, $flashvars = NULL, $params = NULL, $attributes = NULL)
{
	global $dispatch;
	
	$flashvars = ($flashvars) ? json_encode($flashvars) : '{}';
	$params = ($params) ? json_encode($params) : '{}';
	$attributes = ($attributes) ? json_encode($attributes) : '{}';
	
	$swfobject = 'swfobject.embedSWF("' .PATH_COMMON. 'swf/' .$swf. '.swf", "' .$bloc. '", "' .$width. '", "' .$height. '", "9.0.0", false, ' .$flashvars. ', ' .$params. ', ' .$attributes. ');';
	if(isset($dispatch)) $dispatch->addJS($swfobject);
	return $swfobject;
}
?>