<?php
/*
	Fonction getURL
	# R�cup�re l'URL de la page et/ou le nom de la page en cours
	
	$truncateDomain
		TRUE	R�cuperera le nom de la page sans le domaine
		FALSE	R�cup�re l'enti�ret� de l'URL
	$truncateGET
		TRUE	Supprime les variables GET
		FALSE	Laisse les variables GET
*/
function getURL($truncateDomain = false, $truncateGET = true)
{
	if($truncateDomain == false)
	{
		$pageURL = (isset($_SESSION['HTTPS']) and $_SERVER['HTTPS'] == 'on')
			? 'https://'
			: 'http://';
		
		// Si page en local ou non (localhost:80)
		$pageURL .= ($_SERVER["SERVER_PORT"] != '80')
			? $_SERVER["SERVER_NAME"]. ':' .$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"]
			: $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			
		return $pageURL;
	}
	else
	{
		$pageName = explode('/', $_SERVER['REQUEST_URI']);
		$pageName = $pageName[2];
		
		if($truncateGET == true)
		{
			$pageName = explode('?', $pageName);
			$pageName = $pageName[0];
		}
		
		return $pageName;
	}
}
?>