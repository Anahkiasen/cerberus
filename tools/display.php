<?php
/*
	Fonction display
	# Affiche un message d'information/erreur
	
	$message
		Message à afficher
		
	Fonction debug
	# Affiche une variable uniquement dans le code
	
	$message
		La variable/array à afficher dans le code
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