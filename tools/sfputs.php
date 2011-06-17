<?php
function sfputs($file, $content)
{
	$thisFile = fopen($file, 'w+');
	fputs($thisFile, $content);
	fclose($thisFile);
}
?>