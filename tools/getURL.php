<?php
/*
	Fonction getURL
	# Récupère l'URL de la page et/ou le nom de la page en cours
	
	$truncateDomain
		TRUE	Récuperera le nom de la page sans le domaine
		FALSE	Récupère l'entièreté de l'URL
	$truncateGET
		TRUE	Supprime les variables GET
		FALSE	Laisse les variables GET
*/
function getURL($truncateDomain = false, $truncateGET = true)
{
	if(!$truncateDomain)
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
		if(isset($pageName[2]) and !empty($pageName[2])) $pageName = $pageName[2];
		else $pageName = (file_exists('index.php'))
				? 'index.php'
				: 'index.html';
		
		if($truncateGET)
		{
			$pageName = explode('?', $pageName);
			$pageName = $pageName[0];
		}
		
		return $pageName;
	}
}
?>