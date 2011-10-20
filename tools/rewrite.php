<?php
/*
	Fonction rewrite
	# Ecrit une URL avec ses paramètres en prenant compte de l'environnement
	
	@ Dépendance : normalize
	
	$page
		La page vers laquelle aller
	$params
		Paramètres à faire passer
*/
function rewrite($page, $params = NULL)
{
	// Importation des variables
	$GLOBALS['cerberus']->injectModule('normalize');
	global $meta;
	global $navigation;
		
	// Détermination de la page/sous-page
	$hashless = url::strip_hash($page);
	$hash = str_replace($hashless, '', $page);
	$page = $hashless;
	
	if(!is_array($page)) $page = explode('-', $page);
	$page0 = $page[0];
	
	if(isset($page[1])) $page1 = $page[1];
	elseif(!isset($page[1]) and isset($navigation[$page0])) $page1 = $navigation[$page0][0];
	else $page1 = NULL;
	
	// Pas de sous-navigation
	if(is_array($params))
	{
		if(isset($params['subnav']))
		{
			if($params['subnav'] != true) $page1 = NULL;
			unset($params['subnav']);
		}
	
		// Si le nom HTML de la page est fourni
		if(isset($params['html']))
		{
			$thisHTML = $params['html'];
			unset($params['html']);
		}	
	}
	
	if(!REWRITING or $page0 == 'admin')
	{
		// Mode local
		$lien = 'index.php?page=' .$page0;
		if($page1)
		{
			if($page0 == 'admin') $lien .= '&admin=' .$page1;
			else $lien .= '&pageSub=' .$page1;
		}
		if(!empty($params))
		{
			// Si les paramètres sont un array on les implode, sinon on les ajoute en brut
			if(is_array($params)) $lien .= '&' .simplode('=', '&', $params);
			else $lien .= '&' .$params;
		}
	}
	else
	{
		// Mode URL Rewriting
		$lien = $page0. '/';
		if($page1) $lien .= $page1. '/';
	
		if(!empty($params))
		{
			if(is_array($params)) $lien .= simplode('-', '/', $params);
			else $lien .= $params;
			if($lien[strlen($lien)-1] != '/') $lien .= '/';
		}
		$lien = str_replace($page0. '-', '', $lien);
				
		// Si présence du nom HTML de la page (fourni ou dans la base META) on l'ajoute
		$thisPage = $page0. '-' .$page1;
		if(isset($meta[$thisPage]) and !isset($thisHTML)) $thisHTML = (!empty($meta[$thisPage]['url'])) ? $meta[$thisPage]['url'] : $meta[$thisPage]['titre'];
		if(isset($thisHTML))
		{
			$lien .= normalize($thisHTML, true);
			$lien .= '.html';
		}
	}
	
	return $lien.$hash;
}
?>