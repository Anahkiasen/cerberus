<?php
function arrayCSV($array, $filename = 'this', $entete = '')
{
	$csv = $entete;
	$ligne = '';
	if(!is_array($array))
	{	
		while($arrayB = mysql_fetch_assoc($array))
		{
			foreach($arrayB as $key => $value) $ligne .= ($ligne == '') ? '"' .$value. '"' : ',"' .$value. '"';
			if($csv != '') $csv .= '
' .$ligne;
			else $csv .= $ligne;
			$ligne = '';
		}
	}
	else
	{
		foreach($array as $key => $value)
		{
			foreach($value as $linekey => $valuekey) $ligne .= ($ligne == '') ? '"' .$valuekey. '"' : ',"' .$valuekey. '"';
			if($csv != '') $csv .= '
' .$ligne;
			else $csv .= $ligne;
			$ligne = '';
		}
	}
	
	sfputs($filename. '.csv', $csv);
}
function arrayXLS($array, $entete = '', $filename = 'this')
{
	// En-tÃªte
	$fp = fopen($filename. '.xls', "w+");
	fwrite($fp, "\xEF\xBB\xBF");
	$sep = "\t";
	$schema_insert = "";
	$schema_insert_rows = $entete;
	
	fwrite($fp, $schema_insert_rows);
	
	//start while loop to get data
	while($row = mysql_fetch_row($array))
	{
		$schema_insert = "";
		for($j = 0; $j < mysql_num_fields($array); $j++)
		{
			if(!isset($row[$j])) $schema_insert .= "NULL".$sep;
			elseif ($row[$j] != '') $schema_insert .= strip_tags("$row[$j]").$sep;
			else $schema_insert .= $sep;
		}
		$schema_insert = str_replace($sep."$", "", $schema_insert);
		$schema_insert = preg_replace("/\r\n|\n\r|\n|\r/", " ", $schema_insert);
		$schema_insert .= "\n";
		
		fwrite($fp, $schema_insert);
	}
	fclose($fp); 
}
?>