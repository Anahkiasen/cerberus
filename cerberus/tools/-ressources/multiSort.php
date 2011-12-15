<?php
function multiSort($array, $index, $order = 'asc', $natsort = TRUE, $case_sensitive = FALSE) 
{
	if(is_array($array) && count($array) > 0) 
	{
		foreach(array_keys($array) as $key) 
		$temp[$key] = $array[$key][$index];
		if(!$natsort) 
		($order == 'asc') ? asort($temp) : arsort($temp);
		else 
		{
			($case_sensitive) ? natsort($temp) : natcasesort($temp);
			if($order != 'asc') 
			$temp = array_reverse($temp, TRUE);
		}
		
		foreach(array_keys($temp) as $key) (is_numeric($key))? $sorted[] = $array[$key] : $sorted[$key] = $array[$key];
		return $sorted;
	}
	return $array;
}
?>