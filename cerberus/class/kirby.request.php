<?php
class r
{
	static private $_ = false;

	// Récuperation et nettoyage des données
	static function data()
	{
		if(self::$_) return self::$_;
		return self::$_ = self::sanitize($_REQUEST);
	}

	static function sanitize($data)
	{
		foreach($data as $key => $value)
		{
			$value = (!is_array($value))
				? trim(str::stripslashes($value))
				: self::sanitize($value);
			$data[$key] = $value;		
		}			
		return $data;	
	}
	
	static function method()
	{
		return strtoupper(server::get('request_method'));
	}

	static function get($key = FALSE, $default = NULL)
	{
		$request = (self::method() == 'GET') ? self::data() : array_merge(self::data(), self::body());
		if(empty($key)) return $request;
		return a::get($request, $key, $default);
	}
	
	static function set($key, $value = NULL)
	{
		$data = self::data();
		if(is_array($key)) self::$_ = array_merge($data, $key);
		else  self::$_[$key] = $value;
	}
	
	static function body()
	{
		@parse_str(@file_get_contents('php://input'), $body); 
		return self::sanitize((array)$body);
	}

	static function parse()
	{
		$keep	= func_get_args();
		$result = array();
		foreach($keep as $k)
		{
			$params			= explode(':', $k);
			$key			= a::get($params, 0);
			$type			= a::get($params, 1, 'str');
			$default		= a::get($params, 2, '');
			$result[$key] 	= str::sanitize( get($key, $default), $type );
		}
		return $result;
	}

	static function is_ajax()
	{
		return (strtolower(server::get('http_x_requested_with')) == 'xmlhttprequest') ? true : false;
	}
	
	static function is_get()
	{
		return (self::method() == 'GET');
	}
	
	static function is_post()
	{
		return (self::method() == 'POST');
	}
	
	static function is_delete()
	{
		return (self::method() == 'DELETE'); 
	}
	
	static function is_put()
	{
		return (self::method() == 'PUT');
	}

	static function referer($default = NULL)
	{
		if(empty($default)) $default = '/';
		return server::get('http_referer', $default);
	}

}

// Récupérer une valeur GET
function get($key = FALSE, $default = NULL)
{
	return r::get($key, $default);
}
?>