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
	//////////////////////////////////////////////////////////////////
	////////////////////////// INFORMATIONS //////////////////////////
	//////////////////////////////////////////////////////////////////

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
     * Checks if the URL has a query string attached
     *
     * @param  string $url
     * @return boolean
     */
	public static function has_query($url)
	{
		return (str::contains($url, '?'));
	}

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

	/**
     * Checks for a valid URL
     *
     * @param  string $url
     * @return boolean
     */
	public static function valid($url)
	{
		return v::url($url);
	}

	//////////////////////////////////////////////////////////////////
	////////////////////////// EDIT AN URL ///////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
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
     * Strips the query from the URL
     *
     * @param  string $url
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
     * @return string
     */
	public static function strip_hash($url)
	{
		return preg_replace('/#.*$/is', null, $url);
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

	//////////////////////////////////////////////////////////////////
	////////////////////////// VISIT AN URL //////////////////////////
	//////////////////////////////////////////////////////////////////

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
	 * Creates a link to the current page with additional GET parameters
	 *
	 * @param   array   $variables The variables to pass in the URL
	 * @param   boolean $reset     Whether any existing GET parameters should be removed
	 *
	 * @return  string             The resulting URL
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

	//////////////////////////////////////////////////////////////////
	////////////////////////// CREATE AN URL /////////////////////////
	//////////////////////////////////////////////////////////////////

	/**
	 * Uses the website's structure to create an URL from a page index
	 *
	 * @param   string $page   A page index, can be page or page-subpage
	 * @param   array  $params An array of GET parameters to append the URL
	 *
	 * @return  string         An URL, classic or rewritten
	 * @package Cerberus
	 */
	public static function rewrite($page = null, $params = array())
	{
		// Reconstruct the array of parameters if given flat
		if(!is_array($params) and $params)
		{
			// Separate at each &
			$explode_params = explode('&', $params);

			// Reassign each parameter into an array
			$params = array();
			foreach($explode_params as $p)
			{
				$p = explode('=', $p);

				// Single key or key/value
				if(sizeof($p) == 1) $params[$p[0]] = true;
				else $params[$p[0]] = $p[1];
			}
		}

		// Retrieve parameters ------------------------------------- /

		// If we were given a hash in the index, separate it
		$noHash = url::strip_hash($page);
		$hash   = str_replace($noHash, null, $page);
		$page   = $noHash;

		// If no page was given, use the current one
		if(!$page) $page = navigation::current();

		// Explode the index to get page and subpage
		if(!is_array($page)) $page = explode('-', $page);

		// Get page
		$pageMain = a::get($page, 0);

		// Get subpage
		$pageSub = a::get($page, 1);
		if(!$pageSub)
		{
			// Or if no subpage given, search first page available in submenu
			$submenu = a::get(navigation::get($pageMain), 'submenu');
			$pageSub = $submenu ? key($submenu) : null;
		}

		// If we were given a specific name for URL rewriting
		if(isset($params['html']))
		{
			$pageRename = $params['html'];
			$params = a::remove($params, 'html');
		}

		// Classic link writing ------------------------------------ /

		$link = null;

		// Assign pageMain and pageSub as GET parameters
		if($pageMain) $params['page'] = $pageMain;
		if($pageSub)
		{
			if($pageMain == 'admin') $params['admin'] = $pageSub;
			else $params['pageSub'] = $pageSub;
		}

		// Classic HTML link (no REWRITING or in the admin area)
		if(!REWRITING or $pageMain == 'admin')
		{
			if(!empty($params))
				foreach($params as $key => $value) if(!empty($key))
				{
					$link .= !$link ? '?' : '&';
					$link .= is_bool($value) ? $key : $key. '=' .$value;
				}

			$link = 'index.php'.$link;
			return $link.$hash;
		}

		// URL Rewriting ------------------------------------------- /

		// Append page and subpage
		if($pageMain) $link .= $pageMain. '/';
		if($pageSub)  $link .= $pageSub. '/';
		$params = a::remove($params, array('page', 'pageSub'));

		// Append parameters
		if(!empty($params))
		{
			if(is_array($params))
			{
				foreach($params as $k => $v)
					$link .= $k.'-'.$v.'/';
			}
			else $link .= $params;

		}

		// Ensure the link ends with a slash
		if(substr($link, -1, 1) != '/') $link .= '/';

		// Parameters bearing the page's name as key are simplified
		// Example : /news/articles/news-5/ will become news/articles/5/
		$link = str::remove($pageMain. '-', $link);

		// If no URL text given, fetch the page's title attribute to use it
		if(!isset($pageRename))
		{
			$meta = meta::page($pageMain. '-' .$pageSub);
			$pageRename =
				a::get($meta, 'url',
				a::get($meta, 'titre',
				l::get('menu-'.navigation::current()
			)));
		}

		// If we found/have a page name, slugify it and append it
		if($pageRename)
			$link .= str::slugify($pageRename). '.html';

		return $link.$hash;
	}
}
