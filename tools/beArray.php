<?php
/*
	Fonction beArray
	# Transforme une variable en array si elle n'en est pas d�j� un
	
	$variable
		La variable � transformer en array
*/
function beArray($variable)
{
	if(!isset($variable)) $return = array();
	else $return = (!is_array($variable)) ? array($variable) : $variable;	
	
	$variable = $return;
	return $return;
}
?>