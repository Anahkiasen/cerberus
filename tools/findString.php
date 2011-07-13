<?php
function findString($needle, $haystack)
{
	if(is_array($needle))
	{
		// Si array de needles
		$result = 0;
		foreach($needle as $value)
		{
			$pos = strpos($haystack, $value);
			if($pos !== false) $result++;
		}
		if($result == count($needle)) return TRUE;
		else return FALSE;
	}
	elseif(is_array($haystack))
	{
		// Si array de haystack
		$result = 0;
		foreach($haystack as $value)
		{
			$pos = strpos($value, $needle);
			if($pos !== false) $result++;
		}
		if($result == count($haystack)) return TRUE;
		else return FALSE;
	}
	else
	{
		// Simple strpos
		$pos = strpos($haystack, $needle);
		if($pos === false) return FALSE;
		else return TRUE;
	}
}
?>