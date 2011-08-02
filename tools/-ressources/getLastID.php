<?php
function getLastID($table)
{
	$result = mysql_fetch_array(mysql_query('SHOW TABLE STATUS LIKE "' .$table. '"'));
	return $result['Auto_increment'];
}
?>