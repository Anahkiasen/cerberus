<?php
/**
 *
 * Request
 *
 * Handles all incoming requests
 *
 * @package Kirby
 */
class r
{
	/**
		* Stores all sanitized request data
		*
		* @var array
		*/
	static private $_ = false;

	// fetch all data from the request and sanitize it
	public static function data()
	{
		if(self::$_) return self::$_;
		return self::$_ = self::sanitize($_REQUEST);
	}

	/**
		* Sanitizes the incoming data
		*
		* @param	array $data
		* @return array
		*/
	public static function sanitize($data)
	{
		foreach($data as $key => $value)
		{
			$value = !is_array($value)
				? trim(str::stripslashes($value))
				: self::sanitize($value);
			$data[$key] = $value;
		}
		return $data;
	}

	/** Sanitize une suite de chaînes selon CHAMP:TYPE:DEFAULT, CHAMP:TYPE:DEFAULT */
	public static function parse()
	{
		$keep	= func_get_args();
		$result = array();
		foreach($keep AS $k)
		{
			$params			= explode(':', $k);
			$key			= a::get($params, 0);
			$type			= a::get($params, 1, 'str');
			$default		= a::get($params, 2, null);
			$result[$key] 	= str::sanitize(self::get($key, $default), $type);
		}
		return $result;
	}

	/**
		* Sets a request value by key
		*
		* @param	mixed	 $key The key to define
		* @param	mixed	 $value The value for the passed key
		*/
	public static function set($key, $value = null)
	{
		$data = self::data();
		if(is_array($key)) self::$_ = array_merge($data, $key);
		else self::$_[$key] = $value;
	}

	/**
		* Gets a request value by key
		*
		* @param	mixed		$key The key to look for. Pass false or null to return the entire request array.
		* @param	mixed		$default Optional default value, which should be returned if no element has been found
		* @return mixed
		*/
	public static function request($key = false, $default = null)
	{
		$request = (self::method() == 'GET') ? self::data() : array_merge(self::data(), self::body());
		if(empty($key)) return $request;
		return a::get($request, $key, $default);
	}

	/**
	 * Gets a request value by key, only in the POST array
	 */
	public static function post($key = null, $default = null)
	{
		if(!isset($_POST)) return false;

		if(!$key) return $_POST;
		else return a::get($_POST, $key, $default);
	}

	/**
	 * Gets a request value by key, only in the GET array
	 */
	public static function get($key = null, $default = null)
	{
		if(!isset($_GET)) return false;
		if(!$key) return $_GET;
		else return a::get($_GET, $key, $default);
	}

	public static function file($key = null)
	{
		if(!isset($_FILES)) return false;
		if(!$key) return $_FILES;
		else return a::get($_FILES, $key);
	}

	/**
		* Returns the current request method
		*
		* @return string POST, GET, DELETE, PUT
		*/
	public static function method()
	{
		return strtoupper(server::get('request_method'));
	}

	/**
		* Returns the request body from POST requests for example
		*
		* @return array
		*/
	public static function body()
	{
		@parse_str(@file_get_contents('php://input'), $body);
		return self::sanitize((array)$body);
	}

	/**
		* Checks if the current request is an AJAX request
		*
		* @return boolean
		*/
	public static function is_ajax()
	{
		return (strtolower(server::get('http_x_requested_with')) == 'xmlhttprequest') ? true : false;
	}

	/**
		* Checks if the current request is a GET request
		*
		* @return boolean
		*/
	public static function is_get()
	{
		return self::method() == 'GET';
	}

	/**
		* Checks if the current request is a POST request
		*
		* @return boolean
		*/
	public static function is_post()
	{
		return self::method() == 'POST';
	}

	/**
		* Checks if the current request is a DELETE request
		*
		* @return boolean
		*/
	public static function is_delete()
	{
		return self::method() == 'DELETE';
	}

	/**
		* Checks if the current request is a PUT request
		*
		* @return boolean
		*/
	public static function is_put()
	{
		return self::method() == 'PUT';
	}

	/**
		* Returns the HTTP_REFERER
		*
		* @param	string	$default Define a default URL if no referer has been found
		* @return string
		*/
	public static function referer($default = null)
	{
		if(empty($default)) $default = '/';
		return server::get('http_referer', $default);
	}
}
