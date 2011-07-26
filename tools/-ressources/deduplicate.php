<?php
function deduplicate($table, $index, $indexbis = '')
{
	// Récupération du tableau
	$t = 0;
	$indexbis = ($indexbis != '') ? ', ' .$indexbis. ' DESC' : '';
	$query = mysql_query('SELECT * FROM ' .$table. ' ORDER BY ' .$index. ' ASC' .$indexbis) or die(mysql_error());
	while($original = mysql_fetch_assoc($query))
	{
		$t++;
		if(!isset($endtable[$original[$index]]))
			$endtable[$original[$index]] = $original;
	}
	
	// Ecriture du CSV
	$csvFile = '';
	$i = 0;
	foreach($endtable as $value)
	{
		$i++;
		$csvFile .= '"' .implode('","', $value). '"
';
	}
	sfputs('deduplicate.csv', $csvFile);
	echo 'Fichier écrit - ' .($t - $i). ' lignes supprimées.';
}
?>