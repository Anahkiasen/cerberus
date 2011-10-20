<?php
class url
{
	// Retourne l'URL actuelle
	static function current()
	{
		$http = (isset($_SESSION['HTTPS']) and server::get('HTTPS') == 'on')
			? 'https://'
			: 'http://';
		
		return $http.server::get('http_host').server::get('request_uri');
	}

	// Raccourcit l'URL
	static function short($url, $chars = false, $base = false, $rep='…')
	{
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

	// Présence de requêtes GET à la fin de l'URL
	function has_query($url)
	{
		return (str::contains($url, '?'));
	}

	// Supprimer les requêtes
	function strip_query($url)
	{
		return preg_replace('/\?.*$/is', '', $url);
	}

	// Supprimer le hash
	static function strip_hash($url)
	{
		return preg_replace('/#.*$/is', '', $url);
	}

	// Vérifier si l'URL est valide
	function valid($url)
	{
		return v::url($url);
	}
	
	// Aller à l'URL indiquée
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
}
?>