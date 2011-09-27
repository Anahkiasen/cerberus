<?php
/*
	Fonction baseref
	# D�termine la racine de tous les fichiers selon le domaine
	
	$array [array]
		Array facultatif pr�cisant si le site est dans un sous-dossier
		et pour quel domaine, au format (DOMAINE => CHEMIN)
*/
function baseref($array = '')
{
	// R�cup�ration des variables
	global $index;
	global $rewriteMode;
	
	// Si pr�sence d'exceptions
	if(!empty($array))
	{
		foreach($array as $key => $value)
			if(findString($key, $_SERVER['HTTP_HOST'])) $return = '/' .$value. '/';
	}
	if(empty($return) and isset($index['http'])) $return = $index['http'];
	
	if($rewriteMode) return '<base href="' .$return. '" />';
}
?>