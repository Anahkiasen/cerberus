<?php
function pointStringArray($string, $sort = true)
{
	$stringex = explode(';', $string);
	foreach($stringex as $value)
	{
		if(!isset($image)) $image = $value;
		else
		{
			$array[$image] = $value;
			unset($image);
		}
	}
	if($sort == true) ksort($array);
	return $array;
}
?>