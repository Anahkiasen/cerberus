<?php
function html($string)
{
	$string = htmlspecialchars($string);
	return $string = stripslashes($string);
}
?>