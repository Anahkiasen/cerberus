<?php
/*
	Fonction display
	# Affiche un message d'information/erreur
	
	$message
		Message à afficher
*/
function display($message)
{
	return '<p class="infoblock">' .$message. '</p>';
}
function debug($message)
{
	if(is_array($message)) return '<div style="display:none">' .print_r($message). '</div>';
	else return '<p style="display:none">' .$message. '</p>';
}
?>