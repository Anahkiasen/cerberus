<?php
function baseref($array = '')
{
	global $index;
	global $rewriteMode;
	$url = $_SERVER['HTTP_HOST'];
	
	if(!empty($array))
	{
		foreach($array as $key => $value)
			if(findString($key,$url)) $return = '/' .$value. '/';
	}
	if(empty($return) and isset($index['http'])) $return = $index['http'];
	
	if($rewriteMode) return '<base href="' .$return. '" />';
}
?>