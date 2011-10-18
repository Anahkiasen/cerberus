<?php
/*
	Fonction baseref
	# Détermine la racine de tous les fichiers selon le domaine
	
	$array [array]
		Array facultatif précisant si le site est dans un sous-dossier
		et pour quel domaine, au format (DOMAINE => CHEMIN)
*/
function baseref($array = NULL)
{
	// Récupération des variables
	global $index;
	
	// Si présence d'exceptions
	if(!empty($array))
	{
		foreach($array as $key => $value)
			if(findString($key, server::get('HTTP_HOST'))) $return = '/' .$value. '/';
	}
	if(empty($return) and isset($index['http'])) $return = $index['http'];
	
	if(REWRITING) return '<base href="' .$return. '" />';
}
?>