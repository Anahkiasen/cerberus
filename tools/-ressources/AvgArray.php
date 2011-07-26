<?php
function AvgArray($array)
{
	return round(array_sum($array), 0) / sizeof($array);  
}
?>