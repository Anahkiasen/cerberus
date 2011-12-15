<?php
function readCSV($filename)
{
	$thisCSV = file_get_contents($filename);
	$thisCSV = explode('
	', $thisCSV);
	
	foreach($thisCSV as $key => $value)
	{
		$line = explode(';', $value);
		$finalCSV[$key] = $line;
	}
	
	return $finalCSV;
}
?>