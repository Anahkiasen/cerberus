<?php
function simplode($glue1, $glue2, $array)
{
	if(is_array($array))
	{
		$plainedArray = array();
		foreach($array as $key => $value)
		{	
			if(is_array($glue1)) $plainedArray[] = $key.$glue1[0].bdd($value).$glue1[1];
			else $plainedArray[] = $key.$glue1.bdd($value);
		}
		return implode($glue2, $plainedArray);
	}
}
?>