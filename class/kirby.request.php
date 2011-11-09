<?php
class r
{
	// Méthode
	static function method()
	{
		return strtoupper(server::get('request_method'));
	}

	// Récupérer une information
	static function get($key = FALSE, $default = NULL)
	{
		$request = (self::method() == 'GET') ? $_REQUEST : array_merge($_REQUEST, self::body());
		if(empty($key)) return $request;
		else
		{
			$value = a::get($request, $key, $default);
			return (!is_array($value)) ? trim(str::stripslashes($value)) : $value;
		}
	}









	static function set($key, $value = NULL) {
		if(is_array($key)) {
			$_REQUEST = array_merge($_REQUEST, $key);
		} else {
			$_REQUEST[$key] = $value;
		}
	}
	


	static function body() {
		@parse_str(@file_get_contents('php://input'), $body);	
		return (array)$body;
	}

	static function parse() {
		$keep	 = func_get_args();
		$result = array();
		foreach($keep AS $k) {
			$params		 = explode(':', $k);
			$key			= a::get($params, 0);
			$type		 = a::get($params, 1, 'str');
			$default		= a::get($params, 2, '');
			$result[$key] = str::sanitize( get($key, $default), $type );
		}
		return $result;
	}

	static function is_ajax() {
		return (strtolower(server::get('http_x_requested_with')) == 'xmlhttprequest') ? true : false;
	}
	
	static function is_get() {
		return (self::method() == 'GET') ? true : false;
	}
	
	static function is_post() {
		return (self::method() == 'POST') ? true : false;	
	}
	
	static function is_delete() {
		return (self::method() == 'DELETE') ? true : false;	
	}
	
	static function is_put() {
		return (self::method() == 'PUT') ? true : false;	
	}

	static function referer($default = NULL) {
		if(empty($default)) $default = '/';
		return server::get('http_referer', $default);
	}

}

// Récupérer une valeur GET
function get($key = FALSE, $default = NULL)
{
	return a::get($_GET, $key, $default);
	//return r::get($key, $default);
}
?>