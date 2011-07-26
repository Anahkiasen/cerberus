<?php
function randomString($length)
{
	$password = '';
	$possible = '0123456789abcdfghjkmnpqrstvwxyzABCDFGHJKLMNPQRSTVWXYZ'; 
	
	for($i = 0; $i < $length; $i++)		
	{ 
		$char = substr($possible, mt_rand(0, strlen($possible)-1), 1);
		if(!strstr($password, $char)) $password .= $char;
	}
	return $password;
}
?>