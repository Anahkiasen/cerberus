<?php
/**
 *
 * Server
 *
 * Makes it more convenient to get variables
 * from the global server array
 *
 * @package Kirby
 */
class server
{
	/**
		* Gets a value from the _SERVER array
		*
		* @param  mixed    $key The key to look for. Pass false or null to return the entire server array.
		* @param  mixed    $default Optional default value, which should be returned if no element has been found
		* @return mixed
		*/
	static function get($key = FALSE, $default = NULL)
	{
		if(empty($key)) return $_SERVER;
		return a::get($_SERVER, str::upper($key), $default);
	}

	/**
	 * Gets the person's current IP
	 *
	 * @return string    An IP address
	 */
	static function ip()
	{
		$headers = array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
		foreach($headers as $h)
		{
			if(array_key_exists($h, $_SERVER) === true)
				foreach(explode(',', self::get($h)) as $ip)
				{
					if(filter_var($ip, FILTER_VALIDATE_IP) !== false)
						return $ip;
				}
		}
	}

	/**
	 * Gets the person's current country and/or country code
	 *
	 * @return array    An array of informations about the person's location
	 */
	static function location($ip = NULL)
	{
		if(!$ip) $ip = self::ip();
		$country = file_get_contents('http://api.ipinfodb.com/v3/ip-country/?key=45af9a9ca62e018ed24abdb22adb47138ef319fa8227d6e1406c87fe7503b734&ip=' .$ip. '&format=json');
		$country = str::parse($country, 'json');
		return $country;
   }
}
?>
