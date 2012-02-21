<?php
class r
{
	static private $_ = false;

	// Fetch all data from the request and sanitize it
	static function data()
	{
		if(self::$_) return self::$_;
		return self::$_ = self::sanitize($_REQUEST);
	}
	
	// Sanitizes the incoming data
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

	/// Sanitize une suite de chaînes selon CHAMP:TYPE:DEFAULT, CHAMP:TYPE:DEFAULT
	static function parse()
	{
		$keep	= func_get_args();
		$result = array();
		foreach($keep AS $k)
		{
			$params			= explode(':', $k);
			$key			= a::get($params, 0);
			$type			= a::get($params, 1, 'str');
			$default		= a::get($params, 2, NULL);
			$result[$key] 	= str::sanitize(get($key, $default), $type);
		}
		return $result;
	}
	
	/*
	########################################
	######### FONCTIONS MOTEUR #############
	######################################## 
	*/
	
	// Sets a request value by key
	static function set($key, $value = NULL)
	{
		$data = self::data();
		if(is_array($key)) self::$_ = array_merge($data, $key);
		else self::$_[$key] = $value;
	}
	
	// Gets a request value by key
	static function get($key = FALSE, $default = NULL)
	{
		$request = (self::method() == 'GET') ? self::data() : array_merge(self::data(), self::body());
		if(empty($key)) return $request;
		return a::get($request, $key, $default);
	}
	
	// Returns the current request method
	static function method()
	{
		return strtoupper(server::get('request_method'));
	}
	
	// Returns the request body from POST requests for example
	static function body()
	{
		@parse_str(@file_get_contents('php://input'), $body); 
		return self::sanitize((array)$body);
	}
	
	// Returns the HTTP_REFERER
	static function referer($default = NULL)
	{
		if(empty($default)) $default = '/';
		return server::get('http_referer', $default);
	}
		
	/*
	########################################
	######### VERIFICATIONS ################
	######################################## 
	*/

	// Checks if the current request is an AJAX request
	static function is_ajax()
	{
		return (strtolower(server::get('http_x_requested_with')) == 'xmlhttprequest');
	}
	
	// Checks if the current request is a GET request	
	static function is_get()
	{
		return (self::method() == 'GET');
	}
	
	// Checks if the current request is a POST request
	static function is_post()
	{
		return (self::method() == 'POST');
	}
	
	// Checks if the current request is a DELETE request
	static function is_delete()
	{
		return (self::method() == 'DELETE'); 
	}
	
	// Checks if the current request is a PUT request
	static function is_put()
	{
		return (self::method() == 'PUT');
	}
}

// Shortcut for r::get()
function get($key = FALSE, $default = NULL)
{
	return r::get($key, $default);
}
?>