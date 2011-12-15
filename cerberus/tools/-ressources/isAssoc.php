<?php
function isAssoc($array)
{
	return ctype_digit(implode('', array_keys($array)));
}
?>