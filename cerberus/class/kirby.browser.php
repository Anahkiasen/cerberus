<?php
/**
 *
 * Browser
 *
 * Browser sniffing is bad - I know!
 * But sometimes this class is very helpful to
 * react on certain browsers and build browser-specific
 * css selectors for example. It's up to you to use it.
 *
 * @package Kirby
 */
class browser
{

	/**
		* The entire user agent string
		*
		* @var string
		*/
	static public $ua = false;

	/**
		* The readable name of the browser
		* For example: "ie"
		*
		* @var string
		*/
	static public $name = false;

	/**
		* The readable browser engine name
		* For example: "webkit"
		*
		* @var string
		*/
	static public $engine = false;

	/**
		* The browser version number
		* For example: "3.6"
		*
		* @var string
		*/
	static public $version = false;

	/**
		* The platform name
		* For example: "mac"
		*
		* @var string
		*/
	static public $platform = false;

	/**
		* True or false if it is a mobile device or not
		*
		* @var boolean
		*/
	static public $mobile = false;

	/**
		* True or false if it is an iOS device or not
		*
		* @var boolean
		*/
	static public $ios = false;

	/**
		* True or false if it is an iPhone or not
		*
		* @var boolean
		*/
	static public $iphone = false;

	/**
		* Returns the name of the browser
		*
		* @param	string	$ua The user agent string
		* @return string	The browser name
		*/
	public static function name($ua = null)
	{
		self::detect($ua);
		return self::$name;
	}

	/**
		* Returns the browser engine
		*
		* @param	string	$ua The user agent string
		* @return string	The browser engine
		*/
	public static function engine($ua = null)
	{
		self::detect($ua);
		return self::$engine;
	}

	/**
		* Returns the browser version
		*
		* @param	string	$ua The user agent string
		* @return string	The browser version
		*/
	public static function version($ua = null)
	{
		self::detect($ua);
		return self::$version;
	}

	/**
		* Returns the platform
		*
		* @param	string	$ua The user agent string
		* @return string	The platform name
		*/
	public static function platform($ua = null)
	{
		self::detect($ua);
		return self::$platform;
	}

	/**
		* Checks if the user agent string is from a mobile device
		*
		* @param	string	$ua The user agent string
		* @return boolean True: mobile device, false: not a mobile device
		*/
	public static function mobile($ua = null)
	{
		self::detect($ua);
		return self::$mobile;
	}

	/**
		* Checks if the user agent string is from an iPhone
		*
		* @param	string	$ua The user agent string
		* @return boolean True: iPhone, false: not an iPhone
		*/
	public static function iphone($ua = null)
	{
		self::detect($ua);
		return self::$iphone;
	}

	/**
		* Checks if the user agent string is from an iOS device
		*
		* @param	string	$ua The user agent string
		* @return boolean True: iOS device, false: not an iOS device
		*/
	public static function ios($ua = null)
	{
		self::detect($ua);
		return self::$ios;
	}

	/**
		* Returns a browser-specific css selector string
		*
		* @param	string	$ua The user agent string
		* @param	boolean $array True: return an array, false: return a string
		* @return mixed
		*/
	public static function css($ua = null, $array = false)
	{
		self::detect($ua);
		$css[] = self::$engine;
		$css[] = self::$name;
		if(self::$version) $css[] = self::$name . str_replace('.', '_', self::$version);
		$css[] = self::$platform;
		return ($array) ? $css : implode(' ', $css);
	}

	/**
		* The core detection method, which parses the user agent string
		*
		* @todo	 add new browser versions
		* @param	string	$ua The user agent string
		* @return array	 An array with all parsed info
		*/
	public static function detect($ua = null)
	{
		$ua = ($ua) ? str::lower($ua) : str::lower(server::get('http_user_agent'));

		// Don't do the detection twice
		if(self::$ua == $ua) return array(
			'name'     => self::$name,
			'engine'   => self::$engine,
			'version'  => self::$version,
			'platform' => self::$platform,
			'agent'    => self::$ua,
			'mobile'   => self::$mobile,
			'iphone'   => self::$iphone,
			'ios'      => self::$ios);

		self::$ua       = $ua;
		self::$name     = false;
		self::$engine   = false;
		self::$version  = false;
		self::$platform = false;

		// browser
		if(!preg_match('/opera|webtv/i', self::$ua) && preg_match('/msie\s(\d)/', self::$ua, $array))
		{
			self::$version = $array[1];
			self::$name    = 'ie';
			self::$engine  = 'trident';
		}
		else if(strstr(self::$ua, 'firefox/3.6'))
		{
			self::$version = 3.6;
			self::$name    = 'fx';
			self::$engine  = 'gecko';
		}
		else if(strstr(self::$ua, 'firefox/3.5'))
		{
			self::$version = 3.5;
			self::$name    = 'fx';
			self::$engine  = 'gecko';
		}
		else if(preg_match('/firefox\/(\d+)/i', self::$ua, $array))
		{
			self::$version = $array[1];
			self::$name    = 'fx';
			self::$engine  = 'gecko';
		}
		else if(preg_match('/opera(\s|\/)(\d+)/', self::$ua, $array))
		{
			self::$engine  = 'presto';
			self::$name    = 'opera';
			self::$version = $array[2];
		}
		else if(strstr(self::$ua, 'konqueror'))
		{
			self::$name   = 'konqueror';
			self::$engine = 'webkit';
		}
		else if(strstr(self::$ua, 'iron'))
		{
			self::$name   = 'iron';
			self::$engine = 'webkit';
		}
		else if(strstr(self::$ua, 'chrome'))
		{
			self::$name   = 'chrome';
			self::$engine = 'webkit';
			if(preg_match('/chrome\/(\d+)/i', self::$ua, $array)) self::$version = $array[1];
		}
		else if(strstr(self::$ua, 'applewebkit/'))
		{
			self::$name   = 'safari';
			self::$engine = 'webkit';
			if(preg_match('/version\/(\d+)/i', self::$ua, $array)) self::$version = $array[1];
		}
		else if(strstr(self::$ua, 'mozilla/'))
		{
			self::$engine = 'gecko';
			self::$name   = 'fx';
		}

		// Platform
		     if(strstr(self::$ua, 'j2me'))    self::$platform = 'mobile';
		else if(strstr(self::$ua, 'iphone'))  self::$platform = 'iphone';
		else if(strstr(self::$ua, 'ipod'))    self::$platform = 'ipod';
		else if(strstr(self::$ua, 'ipad'))    self::$platform = 'ipad';
		else if(strstr(self::$ua, 'mac'))     self::$platform = 'mac';
		else if(strstr(self::$ua, 'darwin'))  self::$platform = 'mac';
		else if(strstr(self::$ua, 'webtv'))   self::$platform = 'webtv';
		else if(strstr(self::$ua, 'win'))     self::$platform = 'win';
		else if(strstr(self::$ua, 'freebsd')) self::$platform = 'freebsd';
		else if(strstr(self::$ua, 'x11') || strstr(self::$ua, 'linux')) self::$platform = 'linux';

		self::$mobile = (self::$platform == 'mobile') ? true : false;
		self::$iphone = (in_array(self::$platform, array('ipod', 'iphone'))) ? true : false;
		self::$ios    = (in_array(self::$platform, array('ipod', 'iphone', 'ipad'))) ? true : false;

		return array(
			'name'     => self::$name,
			'engine'   => self::$engine,
			'version'  => self::$version,
			'platform' => self::$platform,
			'agent'    => self::$ua,
			'mobile'   => self::$mobile,
			'iphone'   => self::$iphone,
			'ios'      => self::$ios);
	}
}
