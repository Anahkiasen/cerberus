<?php
class url
{
	// Returns the current URL
	static function current()
	{
		$http = (isset($_SESSION['HTTPS']) and server::get('HTTPS') == 'on')
			? 'https://' : 'http://';
		
		return $http.server::get('http_host').server::get('request_uri');
	}
	
	// Shortens an URL
	static function short($url = NULL, $chars = false, $base = false, $rep = '…')
	{
		if(!$url) $url = self::current();
		$url = str_replace('http://','',$url);
		$url = str_replace('https://','',$url);
		$url = str_replace('ftp://','',$url);
		$url = str_replace('www.','',$url);
		
		if($base)
		{
			$a = explode('/', $url);
			$url = a::get($a, 0);
		}
		return ($chars) ? str::short($url, $chars, $rep) : $url;
	}	
	
	// Checks if the URL has a query string attached
	static function has_query($url)
	{
		return (str::contains($url, '?'));
	}	
	
	// Strips the query from the URL
	static function strip_query($url)
	{
		return preg_replace('/\?.*$/is', NULL, $url);
	}	
	
	// Strips a hash value from the URL
	static function strip_hash($url)
	{
		return preg_replace('/#.*$/is', NULL, $url);
	}	

	// Checks for a valid URL
	static function valid($url)
	{
		return v::url($url);
	}	
	
	// Redirects the user to a new URL
	static function go($url = false, $code = false)
	{
		if(empty($url)) $url = config::get('url', '/');
	
		if($code)
		{
			switch($code)
			{
				case 301:
					header('HTTP/1.1 301 Moved Permanently');
					break;
					
				case 302:
					header('HTTP/1.1 302 Found');
					break;
					
				case 303:
					header('HTTP/1.1 303 See Other');
					break;
			}
		}

		header('Location:' .$url);
		exit();
	}
	
	//// Renvoit le domaine du site actuel
	static function domain()
	{
		$base = explode('/', self::short());
		$url = a::get($base, 0);
		if(LOCAL) $url .= '/' .a::get($base, 1);
		return $url.'/';
	}

	//// Vérifie la présence de HTTP:// au début d'une URL
	static function http($url = NULL)
	{
		return 'http://' .str_replace('http://', NULL, ($url));
	}
	
	//// Recharger la page en ajoutant des paramètres supplémentaires
	static function reload($variables = array())
	{
		return self::rewrite(NULL, $variables);
	}
	
	//// Composer une URL depuis un index
	static function rewrite($page = NULL, $params = NULL)
	{
		// Importation des variables
		global $cerberus;
		
		// Détermination de la page/sous-page
		$hashless = url::strip_hash($page);
		$hash = str_replace($hashless, '', $page);
		$page = $hashless;
		
		// Page actuelle
		if(!$page) $page = navigation::current();
				
		if(!is_array($page)) $page = explode('-', $page);
		$page0 = a::get($page, 0);
		
		$submenu = a::get(navigation::get($page0), 'submenu');	
		$page1 = $submenu ? key($submenu) : NULL;
		$page1 = a::get($page, 1, $page1);
		
		// Si le nom HTML de la page est fourni
		if(isset($params['html']))
		{		
			$pageHTML = $params['html'];
			$params = a::remove($params, 'html');
		}
	
		if(!REWRITING or $page0 == 'admin')
		{
			// Mode local
			$lien = 'index.php?page=' .$page0;
			if($page1)
			{
				$lien .= $page0 == 'admin' ? '&admin=' : '&pageSub=';
				$lien .= $page1;
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
			$meta = meta::page($thisPage);
			
			if(!isset($pageHTML))
				$pageHTML =
					a::get($meta, 'url',
					a::get($meta, 'titre',
					l::get('menu-'.$thisPage,
					NULL)));
			
			if($pageHTML)
				$lien .= str::slugify($pageHTML). '.html';
		}
		
		return $lien.$hash;
	}
}
?>