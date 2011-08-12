<?php
function simplode($glue1, $glue2, $array, $convert = true)
{
	if(is_array($array))
	{
		$plainedArray = array();
		foreach($array as $key => $value)
		{	
			$value = ($convert) ? bdd($value) : $value;
			if(is_array($glue1)) $plainedArray[] = $key.$glue1[0].$value.$glue1[1];
			else $plainedArray[] = $key.$glue1.$value;
		}
		return implode($glue2, $plainedArray);
	}
}
?>