<?php
function postVar($post = NULL)
{
	if(empty($post)) $post = $_POST; 
	foreach($_POST as $key => $value)
	{
		$key = str_replace('-', '_', $key);
		if(!isset(${$key})) 
		{
			if(is_array($value)) foreach($value as $k2 => $v2) ${$key}[$k2] = escape($v2);
			else ${$key} = escape($value);
		}
	}
}
?>