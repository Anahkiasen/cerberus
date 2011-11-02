<?php
/*
	Fonction display
	# Retourne un message d'information/erreur
	
	Fonction prompt
	# Affiche un message formaté avec display
	
	Fonction promptm
	# Version multilingue de prompt
	
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
function prompt($message)
{
	echo display($message);
}
function promptm($message, $default = NULL)
{
	echo display(l::get($message, $default));
}
function debug($variable)
{
	$display = (LOCAL) ? NULL : 'display:none';
	
	if(is_array($variable)) return '<div style="' .$display. '"><pre>' .print_r($variable, true). '</pre></div>';
	else return '<p style="' .$display. '">' .$variable. '</p>';
}
?>