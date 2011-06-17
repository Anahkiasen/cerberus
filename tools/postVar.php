<?php
function postVar($post = '')
{
	if($post == '') $post = $_POST; 
	foreach($_POST as $key => $value)
	{
		$key = str_replace('-', '_', $key);
		if(!isset(${$key})) ${$key} = bdd($value);
	}
}
?>