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
function display($message, $type = 'info')
{
	return '<p class="alert alert-' .$type. '">' .$message. '</p>';
}
function prompt($message, $type = 'info')
{
	echo display($message, $type);
}
function promptm($message, $default = NULL, $type = 'info')
{
	echo display(l::get($message, $default), $type);
}
function debug($variable)
{
	$display = (LOCAL) ? NULL : 'display:none';
	
	if(is_array($variable)) return '<div style="' .$display. '"><pre style="' .$display. '">' .print_r($variable, true). '</pre></div>';
	else return '<p style="' .$display. '">' .$variable. '</p>';
}
?>