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
function rewrite($page = NULL, $params = NULL)
{
	// Importation des variables
	global $cerberus, $desired;
	
	// Détermination de la page/sous-page
	$hashless = url::strip_hash($page);
	$hash = str_replace($hashless, '', $page);
	$page = $hashless;
	
	// Page actuelle
	if(!$page)
	{
		global $desired;
		$page = $desired->current();
	}
			
	if(!is_array($page)) $page = explode('-', $page);
	$page0 = a::get($page, 0);
	
	if(isset($page[1])) $page1 = $page[1];
	else $page1 = a::get($desired->get($page0), 0, NULL);
	
	if(is_array($params))
	{
		// Pas de sous-navigation
		if(isset($params['subnav'])) unset($params['subnav']);
	
		// Si le nom HTML de la page est fourni
		if(isset($params['html']))
		{
			$pageHTML = $params['html'];
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
			if(is_array($params)) $lien .= '&' .a::simplode('=', '&', $params);
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
			if(is_array($params)) $lien .= a::simplode('-', '/', $params);
			else $lien .= $params;
			if($lien[strlen($lien)-1] != '/') $lien .= '/';
		}
		$lien = str_replace($page0. '-', '', $lien);
				
		// Si présence du nom HTML de la page (dans admin-meta) on l'ajoute
		$thisPage = $page0. '-' .$page1;
		$meta = $cerberus->meta($thisPage);
		
		if(isset($meta) and !isset($pageHTML))
		{
			$meta_url = trim($meta['url']);
			$pageHTML = (!empty($meta_url)) ? $meta_url : $meta['titre'];
		}
		if(isset($pageHTML) and !empty($pageHTML))
			$lien .= str::slugify($pageHTML, true). '.html';
	}
	
	return $lien.$hash;
}
?>