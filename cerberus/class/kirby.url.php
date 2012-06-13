<?php
/**
 * URL
 *
 * A bunch of handy methods to work with URLs
 *
 * @package Kirby
 */
class url
{
	/**
     * Returns the current URL
     *
     * @return string
     */
  	public static function current()
	{
		$http = (isset($_SESSION['HTTPS']) and server::get('HTTPS') == 'on')
			? 'https://' : 'http://';

		return $http.server::get('http_host').server::get('request_uri');
	}

	/**
	/**
	 * Get the hash from a link
	 *
	 * @param  string $url An url
	 * @return string A hashtag
	 */
	public function get_hash($url)
	{
		$hash = preg_replace('/(.+)#(.+)$/', '$1', $url);
		if($hash == $url) $hash = null;

		return $hash;
	}
     * Shortens an URL
     * It removes http:// or https:// and uses str::short afterwards
     *
     * @param  string  $url   The URL to be shortened
     * @param  int     $chars The final number of characters the URL should have
     * @param  boolean $base  True: only take the base of the URL.
     * @param  string  $rep   The element, which should be added if the string is too long. Ellipsis is the default.
     *
     * @return string The shortened URL
     */
	public static function short($url = null, $chars = false, $base = false, $rep = '…')
	{
		if(!$url) $url = self::current();
		$url = str::remove('http://',  $url);
		$url = str::remove('https://', $url);
		$url = str::remove('ftp://',   $url);
		$url = str::remove('www.',     $url);

		if($base)
		{
			$a = explode('/', $url);
			$url = a::get($a, 0);
		}
		return ($chars) ? str::short($url, $chars, $rep) : $url;
	}

	/**
     * Checks if the URL has a query string attached
     *
     * @param  string $url
     *
     * @return boolean
     */
	public static function has_query($url)
	{
		return (str::contains($url, '?'));
	}

	/**
     * Strips the query from the URL
     *
     * @param  string $url
     *
     * @return string
     */
	public static function strip_query($url)
	{
		return preg_replace('/\?.*$/is', null, $url);
	}

	/**
     * Strips a hash value from the URL
     *
     * @param  string $url
     *
     * @return string
     */
	public static function strip_hash($url)
	{
		return preg_replace('/#.*$/is', null, $url);
	}

	/**
     * Checks for a valid URL
     *
     * @param  string $url
     *
     * @return boolean
     */
	public static function valid($url)
	{
		return v::url($url);
	}

	/**
	 * Redirects the user to a new URL
	 *
	 * @param string  $url  The URL to redirect to
	 * @param boolean $code The HTTP status code, which should be sent (301, 302 or 303)
	 */
	public static function go($url = false, $code = false)
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

	/**
	 * Returns the current domain
	 *
	 * @return string The current domain
	 */
	public static function domain()
	{
		$base = explode('/', self::short());
		$url = a::get($base, 0);
		if(LOCAL) $url .= '/' .a::get($base, 1);
		return $url.'/';
	}

	/**
	 * Ensures that HTTP:// is present at the beginning of a link. Avoid unvoluntary relative paths
	 *
	 * @param  string $url The URL to check
	 *
	 * @return string The corrected URL
	 * @package Cerberus
	 */
	public static function http($url = null)
	{
		return 'http://' .str_replace('http://', null, ($url));
	}

	/**
	 * Creates a link to the current page with additional GET parameters
	 *
	 * @param  array   $variables The variables to pass in the URL
	 * @param  boolean $reset     Whether any existing GET parameters should be removed
	 *
	 * @return string             The resulting URL
	 * @package Cerberus
	 */
	public static function reload($variables = array(), $reset = false)
	{
		if(is_array($variables) and !$reset)
		{
			$get = a::remove($_GET, array('page', 'pageSub', 'admin'));
			$variables = array_merge($get, $variables);
		}
		return self::rewrite(null, $variables);
	}

	/**** Composer une URL depuis un index */
	public static function rewrite($page = null, $params = array())
	{
		// Création du tableau des paramètres
		if(!is_array($params) and $params)
		{
			$explode_params = explode('&', $params);
			$params = array();
			foreach($explode_params as $p)
			{
				$p = explode('=', $p);
				if(sizeof($p) != 1) $params[$p[0]] = $p[1];
				else $params[$p[0]] = true;
			}
		}

		// Détermination de la page/sous-page
		$hashless = url::strip_hash($page);
		$hash = str_replace($hashless, null, $page);
		$page = $hashless;

		// Page actuelle
		if(!$page) $page = navigation::current();

		if(!is_array($page)) $page = explode('-', $page);
		$page0 = a::get($page, 0);

		$submenu = a::get(navigation::get($page0), 'submenu');
		$page1 = $submenu ? key($submenu) : null;
		$page1 = a::get($page, 1, $page1);

		// Si le nom HTML de la page est fourni
		if(isset($params['html']))
		{
			$pageHTML = $params['html'];
			$params = a::remove($params, 'html');
		}

		// Ecriture du lien
		$lien = null;
		if($page0) $params['page'] = $page0;
		if($page1)
		{
			if($page0 == 'admin') $params['admin'] = $page1;
			else $params['pageSub'] = $page1;
		}

		if(!REWRITING or $page0 == 'admin')
		{
			if(!empty($params))
				foreach($params as $key => $value) if(!empty($key))
				{
					$lien .= !$lien ? '?' : '&';
					$lien .= is_bool($value) ? $key : $key. '=' .$value;
				}
				$lien = 'index.php'.$lien;
		}
		else
		{
			$this_page = $page0.'-'.$page1;
			if(isset($params['page'])) $lien .= $params['page']. '/';
			if(isset($params['pageSub'])) $lien .= $params['pageSub']. '/';
			$params = a::remove($params, array('page', 'pageSub'));

			if(!empty($params))
			{
				if(is_array($params))
					foreach($params as $k => $v) $lien .= $k.'-'.$v.'/';
				else $lien .= $params;
				if(substr($lien, -1, 1) != '/') $lien .= '/';
			}
			$lien = str_replace($page0. '-', null, $lien);

			// Si présence du nom HTML de la page (dans admin-meta) on l'ajoute
			$meta = meta::page($this_page);
			if(!isset($pageHTML))
				$pageHTML =
					a::get($meta, 'url',
					a::get($meta, 'titre',
					l::get('menu-'.navigation::current(),
					null)));

			if($pageHTML)
				$lien .= str::slugify($pageHTML). '.html';
		}

		return $lien.$hash;
	}
}
