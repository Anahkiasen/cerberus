<?php
function beArray($variable)
{
	return (!is_array($variable)) ? array($variable) : $variable;	
}
?>