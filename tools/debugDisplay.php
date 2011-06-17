<?php
function debugDisplay($supplementary)
{
	error_reporting(E_ALL);
	
	printr($_SESSION);
	printr($_COOKIE);
	printr($_POST);
	printr($_GET);
	
	if(is_array($supplementary)) printr($supplementary);
	else echo $supplementary;
}
?>