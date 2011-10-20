<?php
/*
	Fonction baseref
	# D�termine la racine de tous les fichiers selon le domaine
	
	$array [array]
		Array facultatif pr�cisant si le site est dans un sous-dossier
		et pour quel domaine, au format (DOMAINE => CHEMIN)
*/
function baseref($array = NULL)
{
	// Si pr�sence d'exceptions
	if(!empty($array))
	{
		foreach($array as $key => $value)
			if(findString($key, server::get('HTTP_HOST'))) $return = '/' .$value. '/';
	}
	if(empty($return)) $return = config::get('http');
	
	if(REWRITING) return '<base href="' .$return. '" />';
}
?>